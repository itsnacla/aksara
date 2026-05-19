<?php

namespace App\Filament\Resources\Grades;

use App\Filament\Resources\Grades\Pages\ManageGrades;
use App\Models\Grade;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class GradeResource extends Resource
{
    protected static ?string $model = Grade::class;

    protected static ?string $recordTitleAttribute = 'id';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static UnitEnum|string|null $navigationGroup = 'Akademik & KBM';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Penilaian';

    protected static ?string $modelLabel = 'Penilaian';

    protected static ?string $pluralModelLabel = 'Penilaian';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('subject_id')
                ->relationship('subject', 'nama_mapel', modifyQueryUsing: function ($query) {
                    $query->where('subjects.is_graded', true);
                    $user = auth()->user();
                    if ($user && $user->hasRole('guru') && !$user->hasAnyRole(['super_admin', 'staff']) && $user->teacher) {
                        $teacherId = $user->teacher->id;
                        $managedLevelIds = \App\Models\StudyGroup::where('walikelas_id', $teacherId)->pluck('level_id')->toArray();
                        $query->where(function ($q) use ($teacherId, $managedLevelIds) {
                            $q->whereHas('schedules', fn ($sq) => $sq->where('teacher_id', $teacherId))
                              ->orWhereHas('levels', fn ($lq) => $lq->whereIn('levels.id', $managedLevelIds));
                        });
                    }
                })
                ->preload()
                ->searchable()
                ->required()
                ->live()
                ->afterStateUpdated(function (callable $set) {
                    $set('study_group_id', null);
                    $set('student_id', null);
                }),
            Select::make('study_group_id')
                ->relationship('studyGroup', 'nama_rombel', modifyQueryUsing: function ($query, callable $get) {
                    $subjectId = $get('subject_id');
                    $user = auth()->user();
                    if ($user && $user->hasRole('guru') && !$user->hasAnyRole(['super_admin', 'staff']) && $user->teacher) {
                        $teacherId = $user->teacher->id;
                        $query->where(function ($q) use ($teacherId, $subjectId) {
                            if ($subjectId) {
                                $q->whereHas('schedules', fn ($sq) => $sq->where('teacher_id', $teacherId)->where('subject_id', $subjectId));
                            } else {
                                $q->whereHas('schedules', fn ($sq) => $sq->where('teacher_id', $teacherId));
                            }
                            $q->orWhere('walikelas_id', $teacherId);
                        });
                    }
                })
                ->preload()
                ->searchable()
                ->required()
                ->live()
                ->afterStateUpdated(function (callable $set) {
                    $set('student_id', null);
                }),
            Select::make('student_id')
                ->label('Siswa')
                ->options(function (callable $get) {
                    $studyGroupId = $get('study_group_id');
                    if (!$studyGroupId) {
                        return [];
                    }
                    return \App\Models\Student::whereHas('studyGroups', fn ($q) => $q->where('study_groups.id', $studyGroupId))
                        ->with('user')
                        ->get()
                        ->mapWithKeys(fn ($student) => [$student->id => "{$student->nisn} - {$student->user->name}"])
                        ->toArray();
                })
                ->searchable()
                ->preload()
                ->required()
                ->live(),
            \Filament\Forms\Components\Hidden::make('academic_year_id')
                ->default(fn () => \App\Models\AcademicYear::where('is_active', true)->first()?->id),
            \Filament\Schemas\Components\Grid::make(3)
                ->schema([
                    TextInput::make('nilai_tugas')->numeric()->minValue(0)->maxValue(100)->required(),
                    TextInput::make('nilai_uts')->numeric()->minValue(0)->maxValue(100)->required(),
                    TextInput::make('nilai_uas')->numeric()->minValue(0)->maxValue(100)->required(),
                ]),
                
            \Filament\Schemas\Components\Section::make('Capaian Kompetensi (Tujuan Pembelajaran)')
                ->schema([
                    \Filament\Forms\Components\CheckboxList::make('optimal_tp_ids')
                        ->label('TP Yang diukur dan Tercapai dengan Optimal')
                        ->options(function (callable $get) {
                            $subjectId = $get('subject_id');
                            $studyGroupId = $get('study_group_id');
                            if (!$subjectId || !$studyGroupId) {
                                return [];
                            }
                            $studyGroup = \App\Models\StudyGroup::find($studyGroupId);
                            if (!$studyGroup) {
                                return [];
                            }
                            return \App\Models\LearningObjective::where('subject_id', $subjectId)
                                ->where('level_id', $studyGroup->level_id)
                                ->where('is_active', true)
                                ->get()
                                ->pluck('description', 'id')
                                ->mapWithKeys(fn ($desc, $id) => [$id => "{$desc}"])
                                ->toArray();
                        })
                        ->live()
                        ->afterStateUpdated(function (callable $set, callable $get, $state) {
                            $improved = $get('improved_tp_ids') ?? [];
                            $filteredImproved = array_values(array_diff($improved, $state ?? []));
                            $set('improved_tp_ids', $filteredImproved);
                        })
                        ->bulkToggleable()
                        ->columns(2),

                    \Filament\Forms\Components\CheckboxList::make('improved_tp_ids')
                        ->label('TP yang diukur dan Perlu Peningkatan')
                        ->options(function (callable $get) {
                            $subjectId = $get('subject_id');
                            $studyGroupId = $get('study_group_id');
                            if (!$subjectId || !$studyGroupId) {
                                return [];
                            }
                            $studyGroup = \App\Models\StudyGroup::find($studyGroupId);
                            if (!$studyGroup) {
                                return [];
                            }
                            return \App\Models\LearningObjective::where('subject_id', $subjectId)
                                ->where('level_id', $studyGroup->level_id)
                                ->where('is_active', true)
                                ->get()
                                ->pluck('description', 'id')
                                ->mapWithKeys(fn ($desc, $id) => [$id => "{$desc}"])
                                ->toArray();
                        })
                        ->live()
                        ->afterStateUpdated(function (callable $set, callable $get, $state) {
                            $optimal = $get('optimal_tp_ids') ?? [];
                            $filteredOptimal = array_values(array_diff($optimal, $state ?? []));
                            $set('optimal_tp_ids', $filteredOptimal);
                        })
                        ->bulkToggleable()
                        ->columns(2),
                ])
                ->visible(fn (callable $get) => filled($get('subject_id')) && filled($get('study_group_id'))),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.user.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('studyGroup.nama_rombel')
                    ->label('Rombel')
                    ->sortable(),
                TextColumn::make('subject.nama_mapel')
                    ->label('Mapel')
                    ->sortable(),
                TextColumn::make('nilai_tugas')
                    ->label('Tugas')
                    ->numeric(),
                TextColumn::make('nilai_uts')
                    ->label('UTS')
                    ->numeric(),
                TextColumn::make('nilai_uas')
                    ->label('UAS')
                    ->numeric(),
                TextColumn::make('total_nilai')
                    ->label('Rata-rata')
                    ->state(fn ($record) => round(($record->nilai_tugas + $record->nilai_uts + $record->nilai_uas) / 3, 2))
                    ->badge()
                    ->color(fn ($state) => $state >= 75 ? 'success' : ($state >= 60 ? 'warning' : 'danger')),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->relationship('academicYear', 'tahun_ajaran')
                    ->default(fn () => \App\Models\AcademicYear::where('is_active', true)->first()?->id),
                \Filament\Tables\Filters\SelectFilter::make('study_group_id')
                    ->label('Rombel')
                    ->relationship('studyGroup', 'nama_rombel', modifyQueryUsing: function ($query) {
                        $user = auth()->user();
                        if ($user && $user->hasRole('guru') && !$user->hasAnyRole(['super_admin', 'staff']) && $user->teacher) {
                            $teacherId = $user->teacher->id;
                            $query->where(function ($q) use ($teacherId) {
                                $q->whereHas('schedules', fn ($sq) => $sq->where('teacher_id', $teacherId))
                                  ->orWhere('walikelas_id', $teacherId);
                            });
                        }
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->modalWidth('7xl')
                    ->closeModalByClickingAway(false),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user && $user->hasRole('guru') && !$user->hasAnyRole(['super_admin', 'staff']) && $user->teacher) {
            $teacherId = $user->teacher->id;
            
            $query->where(function ($q) use ($teacherId) {
                // Guru Mapel: Only see grades for the specific subject and Rombel they teach in schedules
                $q->whereExists(function ($subquery) use ($teacherId) {
                    $subquery->select(\DB::raw(1))
                        ->from('schedules')
                        ->where('schedules.teacher_id', $teacherId)
                        ->whereColumn('schedules.study_group_id', 'grades.study_group_id')
                        ->whereColumn('schedules.subject_id', 'grades.subject_id');
                })
                // Wali Kelas: See all grades for their Rombel
                ->orWhereHas('studyGroup', fn ($sq) => $sq->where('walikelas_id', $teacherId));
            });
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageGrades::route('/'),
        ];
    }
}
