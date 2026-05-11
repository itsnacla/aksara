<?php

namespace App\Filament\Resources\StudyGroups\Tables;

use App\Models\StudyGroup;
use App\Models\AcademicYear;
use App\Models\Level;
use App\Models\Classroom;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Radio;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;

class StudyGroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(static::getColumns())
            ->filters(static::getFilters())
            ->actions(static::getActions())
            ->bulkActions(static::getBulkActions());
    }

    protected static function getColumns(): array
    {
        return [
            TextColumn::make('nama_rombel')
                ->label('Identitas Rombel')
                ->searchable()
                ->sortable()
                ->weight('bold')
                ->color('primary')
                ->wrap(),
            
            TextColumn::make('waliKelas.user.name')
                ->label('Wali Kelas')
                ->icon('heroicon-m-user-circle')
                ->color('gray')
                ->description(fn ($record) => "NIP/ID: " . ($record->waliKelas?->nip ?? '-'))
                ->sortable(),

            TextColumn::make('students_count')
                ->label('Siswa')
                ->counts('students')
                ->badge()
                ->color('info')
                ->alignCenter(),
        ];
    }

    protected static function getFilters(): array
    {
        return [
            SelectFilter::make('academic_year_id')
                ->label('Filter Tahun Ajaran')
                ->options(fn () => AcademicYear::all()->mapWithKeys(fn ($year) => [
                    $year->id => "Tahun Ajaran {$year->tahun_ajaran}"
                ]))
                ->default(fn () => AcademicYear::where('is_active', true)->first()?->id),
        ];
    }

    protected static function getActions(): array
    {
        return [
            static::getPromoteAction(),
            ViewAction::make(),
            EditAction::make(),
            Action::make('print_cards')
                ->label('Cetak Kartu Siswa')
                ->icon('heroicon-o-identification')
                ->color('info')
                ->url(fn ($record) => route('student.cards.rombel', ['studyGroupId' => $record->id]))
                ->openUrlInNewTab(),
            DeleteAction::make(),
        ];
    }

    protected static function getBulkActions(): array
    {
        return [
            \Filament\Actions\BulkActionGroup::make([
                \Filament\Actions\DeleteBulkAction::make(),
            ]),
        ];
    }

    protected static function getPromoteAction(): Action
    {
        return Action::make('promote_students')
            ->label('Naikkan Kelas')
            ->icon('heroicon-o-arrow-trending-up')
            ->visible(fn () => auth()->user()?->hasAnyRole(['super_admin', 'staff']))
            ->color('info')
            ->link()
            ->modalHeading('Proses Kenaikan Kelas')
            ->modalWidth('xl')
            ->form([
                Radio::make('mode')
                    ->label('Mode Kenaikan')
                    ->options([
                        'existing' => 'Pilih Rombel yang Sudah Ada',
                        'create' => 'Buat Rombel Baru Otomatis',
                    ])
                    ->default('existing')
                    ->reactive(),

                Select::make('target_academic_year_id')
                    ->label('Tahun Ajaran Tujuan')
                    ->options(AcademicYear::all()->mapWithKeys(fn ($y) => [$y->id => "Tahun Ajaran {$y->tahun_ajaran}"]))
                    ->required()
                    ->live()
                    ->searchable(),

                Select::make('target_study_group_id')
                    ->label('Pilih Rombel Tujuan')
                    ->options(function (callable $get, $record) {
                        if ($get('mode') !== 'existing') return [];
                        
                        return StudyGroup::where('id', '!=', $record->id)
                            ->where('academic_year_id', $get('target_academic_year_id'))
                            ->with(['level'])
                            ->get()
                            ->mapWithKeys(fn ($group) => [
                                $group->id => "{$group->nama_rombel} ({$group->level->nama_tingkatan})"
                            ]);
                    })
                    ->hidden(fn (callable $get) => $get('mode') !== 'existing')
                    ->required(fn (callable $get) => $get('mode') === 'existing')
                    ->searchable(),

                Grid::make(2)
                    ->schema([
                        Select::make('new_level_id')
                            ->label('Tingkatan Baru')
                            ->options(Level::all()->pluck('nama_tingkatan', 'id'))
                            ->default(function($record) {
                                $nextLevel = Level::where('id', '>', $record->level_id)->first();
                                return $nextLevel ? $nextLevel->id : $record->level_id;
                            })
                            ->required(fn (callable $get) => $get('mode') === 'create'),
                        Select::make('new_walikelas_id')
                            ->label('Wali Kelas Baru')
                            ->options(function (callable $get) {
                                $academicYearId = $get('target_academic_year_id');
                                if (!$academicYearId) return [];

                                return \App\Models\Teacher::with('user')
                                    ->where('is_walikelas', true)
                                    ->where('status', 'aktif')
                                    ->whereHas('user', fn($q) => $q->where('is_active', true))
                                    ->whereDoesntHave('studyGroups', function ($query) use ($academicYearId) {
                                        $query->where('academic_year_id', $academicYearId);
                                    })
                                    ->get()
                                    ->pluck('user.name', 'id');
                            })
                            ->searchable()
                            ->reactive()
                            ->required(fn (callable $get) => $get('mode') === 'create')
                            ->helperText('Hanya menampilkan guru aktif yang belum ditugaskan.'),
                        TextInput::make('new_classroom_name')
                            ->label('Nama Ruangan Tujuan')
                            ->placeholder('Contoh: Ruang 3A')
                            ->default(function($record) {
                                if (!$record->classroom) return '';
                                return preg_replace_callback('/\d+/', function($m) { return $m[0] + 1; }, $record->classroom->nama_ruangan);
                            })
                            ->helperText('Sistem menebak ruangan berikutnya.')
                            ->required(fn (callable $get) => $get('mode') === 'create')
                            ->columnSpanFull(),
                    ])
                    ->hidden(fn (callable $get) => $get('mode') !== 'create'),
            ])
            ->action(function ($record, array $data) {
                $targetGroupId = null;

                if ($data['mode'] === 'create') {
                    $classroom = Classroom::firstOrCreate([
                        'nama_ruangan' => $data['new_classroom_name']
                    ]);

                    $newGroup = StudyGroup::create([
                        'academic_year_id' => $data['target_academic_year_id'],
                        'level_id' => $data['new_level_id'],
                        'classroom_id' => $classroom->id,
                        'walikelas_id' => $data['new_walikelas_id'],
                    ]);
                    $targetGroupId = $newGroup->id;
                } else {
                    $targetGroupId = $data['target_study_group_id'];
                }

                $students = $record->students;
                $studentCount = $students->count();
                
                foreach ($students as $student) {
                    $student->studyGroups()->syncWithoutDetaching([$targetGroupId]);
                }

                Notification::make()
                    ->title('Proses Berhasil!')
                    ->success()
                    ->body("Berhasil mempromosikan {$studentCount} siswa ke Rombel tujuan.")
                    ->send();
            });
    }
}
