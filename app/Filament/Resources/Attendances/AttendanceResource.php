<?php

namespace App\Filament\Resources\Attendances;

use App\Filament\Resources\Attendances\Pages\CreateAttendance;
use App\Filament\Resources\Attendances\Pages\EditAttendance;
use App\Filament\Resources\Attendances\Pages\ListAttendances;
use App\Models\Attendance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use UnitEnum;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $recordTitleAttribute = 'catatan';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-check-badge';

    protected static UnitEnum|string|null $navigationGroup = 'Akademik & KBM';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Presensi Siswa';

    protected static ?string $modelLabel = 'Presensi Siswa';

    protected static ?string $pluralModelLabel = 'Presensi Siswa';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('study_group_id')
                ->label('Rombel')
                ->relationship('studyGroup', 'nama_rombel', fn ($query) => $query->whereHas('academicYear', fn ($q) => $q->where('is_active', true)))
                ->live()
                ->afterStateUpdated(fn (\Filament\Schemas\Components\Utilities\Set $set) => $set('student_id', null))
                ->required(),
            Select::make('student_id')
                ->label('Siswa')
                ->options(function (\Filament\Schemas\Components\Utilities\Get $get) {
                    $studyGroupId = $get('study_group_id');
                    if (!$studyGroupId) {
                        return [];
                    }
                    return \App\Models\Student::with('user')
                        ->where('status', 'aktif')
                        ->whereHas('studyGroups', fn ($q) => $q->where('study_groups.id', $studyGroupId))
                        ->get()
                        ->pluck('user.name', 'id');
                })
                ->searchable()
                ->required(),
            DatePicker::make('tanggal')
                ->default(now())
                ->required(),
            Select::make('status')
                ->options([
                    'hadir' => 'Hadir',
                    'sakit' => 'Sakit',
                    'izin' => 'Izin',
                    'alpha' => 'Alpha',
                ])
                ->required(),
            TextInput::make('catatan'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal')
                    ->date()
                    ->sortable(),
                TextColumn::make('student.user.name')
                    ->label('Siswa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('studyGroup.nama_rombel')
                    ->label('Rombel')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'hadir' => 'success',
                        'sakit' => 'warning',
                        'izin' => 'info',
                        'alpha' => 'danger',
                    }),
                TextColumn::make('check_in')
                    ->label('Masuk')
                    ->dateTime('H:i')
                    ->sortable(),
                TextColumn::make('check_out')
                    ->label('Pulang')
                    ->dateTime('H:i')
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\Filter::make('rombel_filter')
                    ->form([
                        Select::make('academic_year_id')
                            ->label('Tahun Ajaran')
                            ->options(fn () => \App\Models\AcademicYear::all()->mapWithKeys(fn ($year) => [
                                $year->id => "Tahun Ajaran {$year->tahun_ajaran} (" . ucfirst($year->semester) . ")"
                            ]))
                            ->default(fn () => \App\Models\AcademicYear::where('is_active', true)->first()?->id)
                            ->live(),
                        Select::make('study_group_id')
                            ->label('Rombel')
                            ->options(function (\Filament\Schemas\Components\Utilities\Get $get) {
                                $academicYearId = $get('academic_year_id');
                                if (!$academicYearId) return \App\Models\StudyGroup::pluck('nama_rombel', 'id');
                                return \App\Models\StudyGroup::where('academic_year_id', $academicYearId)->pluck('nama_rombel', 'id');
                            })
                            ->searchable(),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when(
                                $data['academic_year_id'] ?? null,
                                fn (\Illuminate\Database\Eloquent\Builder $query, $value): \Illuminate\Database\Eloquent\Builder => $query->whereHas('studyGroup', fn ($q) => $q->where('academic_year_id', $value))
                            )
                            ->when(
                                $data['study_group_id'] ?? null,
                                fn (\Illuminate\Database\Eloquent\Builder $query, $value): \Illuminate\Database\Eloquent\Builder => $query->where('study_group_id', $value)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['academic_year_id'] ?? null) {
                            $year = \App\Models\AcademicYear::find($data['academic_year_id']);
                            if ($year) {
                                $indicators[] = \Filament\Tables\Filters\Indicator::make('Tahun Ajaran: ' . $year->tahun_ajaran)
                                    ->removeField('academic_year_id');
                            }
                        }
                        if ($data['study_group_id'] ?? null) {
                            $rombel = \App\Models\StudyGroup::find($data['study_group_id']);
                            if ($rombel) {
                                $indicators[] = \Filament\Tables\Filters\Indicator::make('Rombel: ' . $rombel->nama_rombel)
                                    ->removeField('study_group_id');
                            }
                        }
                        return $indicators;
                    }),
                SelectFilter::make('status')
                    ->options([
                        'hadir' => 'Hadir',
                        'sakit' => 'Sakit',
                        'izin' => 'Izin',
                        'alpha' => 'Alpha',
                    ]),
                \Filament\Tables\Filters\Filter::make('tanggal')
                    ->form([
                        DatePicker::make('from')->label('Mulai Tanggal'),
                        DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('tanggal', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('tanggal', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make()->modal(),
                \Filament\Actions\EditAction::make()->modal(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery()->with(['student.user', 'studyGroup']);
        
        $user = auth()->user();
        if ($user && $user->hasRole('guru') && $user->teacher) {
            $teacherId = $user->teacher->id;
            $query->where(function ($q) use ($teacherId) {
                $q->whereHas('studyGroup', fn ($sq) => $sq->where('walikelas_id', $teacherId))
                  ->orWhereHas('schedule', fn ($sq) => $sq->where('teacher_id', $teacherId));
            });
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAttendances::route('/'),
        ];
    }
}
