<?php

namespace App\Filament\Resources\StudyGroups\Tables;

use App\Models\StudyGroup;
use App\Models\AcademicYear;
use App\Models\Level;
use App\Models\Classroom;
use Filament\Infolists\Components\TextEntry;
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
            
            TextColumn::make('waliKelas.nama_lengkap')
                ->label('Wali Kelas')
                ->searchable(['user.name'])
                ->icon('heroicon-m-user-circle')
                ->color('gray')
                ->description(fn ($record) => "NIP/ID: " . ($record->waliKelas?->nip ?? '-'))
                ->sortable()
                ->formatStateUsing(fn ($record) => $record->waliKelas?->nama_lengkap ?? '-'),

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
            DeleteAction::make()
                ->before(function (DeleteAction $action, StudyGroup $record) {
                    $hasStudents = $record->students()->exists();
                    $hasSchedules = $record->schedules()->exists();

                    if ($hasStudents || $hasSchedules) {
                        $relatedItems = [];
                        if ($hasStudents) $relatedItems[] = 'Siswa';
                        if ($hasSchedules) $relatedItems[] = 'Jadwal';

                        Notification::make()
                            ->title('Tidak Dapat Menghapus Rombel')
                            ->danger()
                            ->body('Rombel ini masih memiliki data terkait: ' . implode(', ', $relatedItems) . '. Lepaskan atau hapus data terkait terlebih dahulu.')
                            ->persistent()
                            ->send();

                        $action->cancel();
                    }
                }),
        ];
    }

    protected static function getBulkActions(): array
    {
        return [
            \Filament\Actions\BulkActionGroup::make([
                \Filament\Actions\DeleteBulkAction::make()
                    ->before(function (\Filament\Actions\DeleteBulkAction $action, \Illuminate\Database\Eloquent\Collection $records) {
                        foreach ($records as $record) {
                            if ($record->students()->exists() || $record->schedules()->exists()) {
                                Notification::make()
                                    ->title('Penghapusan Massal Dibatalkan')
                                    ->danger()
                                    ->body("Rombel {$record->nama_rombel} tidak dapat dihapus karena masih memiliki data Siswa atau Jadwal.")
                                    ->persistent()
                                    ->send();
                                $action->cancel();
                            }
                        }
                    }),
            ]),
        ];
    }

    protected static function getPromoteAction(): Action
    {
        return Action::make('promote_students')
            ->label(fn ($record) => $record->level?->is_last_level ? 'Luluskan Siswa' : 'Naikkan Kelas')
            ->icon(fn ($record) => $record->level?->is_last_level ? 'heroicon-o-academic-cap' : 'heroicon-o-arrow-trending-up')
            ->visible(fn () => auth()->user()?->hasAnyRole(['super_admin', 'staff']))
            ->color(fn ($record) => $record->level?->is_last_level ? 'success' : 'info')
            ->link()
            ->modalHeading(fn ($record) => $record->level?->is_last_level ? 'Proses Kelulusan Siswa' : 'Proses Kenaikan Kelas')
            ->modalDescription(fn ($record) => $record->level?->is_last_level ? "Semua siswa di rombel {$record->nama_rombel} akan diubah statusnya menjadi Lulus." : null)
            ->form(function ($record) {
                $studentOptions = $record->students->mapWithKeys(fn ($s) => [$s->id => "{$s->nisn} - {$s->user->name}"]);
                $studentIds = $record->students->pluck('id')->toArray();

                $checklist = \Filament\Forms\Components\CheckboxList::make('selected_students')
                    ->label('Pilih Siswa yang Lulus / Naik Kelas')
                    ->options($studentOptions)
                    ->default($studentIds)
                    ->columns(2)
                    ->required()
                    ->helperText('Siswa yang tidak dipilih akan dianggap tinggal kelas / tetap di status aktif saat ini.');

                if ($record->level?->is_last_level) {
                    return [
                        $checklist,
                        TextEntry::make('info')
                            ->label("Siswa yang dipilih akan diubah statusnya menjadi Lulus.")
                    ];
                }

                return [
                    $checklist,
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
                        ->options(function ($record) {
                            return AcademicYear::where('id', '>', $record->academic_year_id)
                                ->get()
                                ->mapWithKeys(fn ($y) => [$y->id => "Tahun Ajaran {$y->tahun_ajaran} - " . ucfirst($y->semester)]);
                        })
                        ->default(function ($record) {
                            return AcademicYear::where('id', '>', $record->academic_year_id)->first()?->id;
                        })
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
                                        ->pluck('nama_lengkap', 'id');
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
                ];
            })
            ->action(function ($record, array $data) {
                $selectedIds = $data['selected_students'] ?? [];
                $studentsToProcess = $record->students()->whereIn('students.id', $selectedIds)->get();
                $processCount = $studentsToProcess->count();

                if ($record->level?->is_last_level) {
                    foreach ($studentsToProcess as $student) {
                        $student->update(['status' => 'lulus']);
                    }

                    Notification::make()
                        ->title('Kelulusan Berhasil!')
                        ->success()
                        ->body("Berhasil meluluskan {$processCount} siswa dari rombel {$record->nama_rombel}.")
                        ->send();
                    return;
                }

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
                
                foreach ($studentsToProcess as $student) {
                    $student->studyGroups()->syncWithoutDetaching([$targetGroupId]);
                    $student->update(['status' => 'aktif']);
                }

                Notification::make()
                    ->title('Proses Berhasil!')
                    ->success()
                    ->body("Berhasil mempromosikan {$processCount} siswa ke Rombel tujuan.")
                    ->send();
            });
    }
}
