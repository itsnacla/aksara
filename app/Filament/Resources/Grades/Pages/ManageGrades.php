<?php

namespace App\Filament\Resources\Grades\Pages;

use App\Filament\Resources\Grades\GradeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageGrades extends ManageRecords
{
    protected static string $resource = GradeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('batch_input')
                ->label('Batch Input Nilai')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->modalWidth('7xl')
                ->closeModalByClickingAway(false)
                ->form([
                    \Filament\Schemas\Components\Section::make('Filter Mata Pelajaran & Rombel')
                        ->schema([
                            \Filament\Forms\Components\Select::make('subject_id')
                                ->label('Pilih Mapel')
                                ->options(function () {
                                    $user = auth()->user();
                                    $query = \App\Models\Subject::query();
                                    if ($user->hasRole('guru') && $user->teacher) {
                                        $query->whereHas('schedules', fn ($q) => $q->where('teacher_id', $user->teacher->id));
                                    }
                                    return $query->pluck('nama_mapel', 'id');
                                })
                                ->required()
                                ->live(),
                            \Filament\Forms\Components\Select::make('study_group_id')
                                ->label('Pilih Rombel')
                                ->options(function (\Filament\Schemas\Components\Utilities\Get $get) {
                                    $subjectId = $get('subject_id');
                                    if (!$subjectId) return [];
                                    
                                    $user = auth()->user();
                                    $query = \App\Models\StudyGroup::query();
                                    
                                    if ($user->hasRole('guru') && $user->teacher) {
                                        $query->whereHas('schedules', fn ($q) => $q->where('teacher_id', $user->teacher->id)->where('subject_id', $subjectId))
                                              ->orWhere('walikelas_id', $user->teacher->id);
                                    }
                                    
                                    return $query->pluck('nama_rombel', 'id');
                                })
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn (\Filament\Schemas\Components\Utilities\Get $get, \Filament\Schemas\Components\Utilities\Set $set) => self::loadStudentsForGrading($get, $set)),
                        ])->columns(2),
                    
                    \Filament\Schemas\Components\Section::make('Daftar Nilai Siswa')
                        ->schema([
                            \Filament\Forms\Components\Repeater::make('items')
                                ->label('')
                                ->schema([
                                    \Filament\Forms\Components\Hidden::make('student_id'),
                                    \Filament\Schemas\Components\Grid::make(12)
                                        ->schema([
                                            // Left side: Student Name and Grades (4 columns of 12)
                                            \Filament\Schemas\Components\Grid::make(1)
                                                ->schema([
                                                    \Filament\Forms\Components\TextInput::make('student_name')
                                                        ->label('Siswa')
                                                        ->disabled()
                                                        ->dehydrated(false),
                                                    \Filament\Schemas\Components\Grid::make(3)
                                                        ->schema([
                                                            \Filament\Forms\Components\TextInput::make('nilai_tugas')
                                                                ->label('Tugas')
                                                                ->numeric()
                                                                ->minValue(0)
                                                                ->maxValue(100),
                                                            \Filament\Forms\Components\TextInput::make('nilai_uts')
                                                                ->label('UTS')
                                                                ->numeric()
                                                                ->minValue(0)
                                                                ->maxValue(100),
                                                            \Filament\Forms\Components\TextInput::make('nilai_uas')
                                                                ->label('UAS')
                                                                ->numeric()
                                                                ->minValue(0)
                                                                ->maxValue(100),
                                                        ]),
                                                ])
                                                ->columnSpan(4),

                                            // Right side: TP Checklists (8 columns of 12)
                                            \Filament\Schemas\Components\Grid::make(2)
                                                ->schema([
                                                    \Filament\Forms\Components\CheckboxList::make('optimal_tp_ids')
                                                        ->label('TP Tercapai Optimal')
                                                        ->options(function (\Filament\Schemas\Components\Utilities\Get $get) {
                                                            $subjectId = $get('../../subject_id');
                                                            $studyGroupId = $get('../../study_group_id');
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
                                                                ->toArray();
                                                        })
                                                        ->live()
                                                        ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                                            $improved = $get('improved_tp_ids') ?? [];
                                                            $filteredImproved = array_values(array_diff($improved, $state ?? []));
                                                            $set('improved_tp_ids', $filteredImproved);
                                                        })
                                                        ->bulkToggleable(),

                                                    \Filament\Forms\Components\CheckboxList::make('improved_tp_ids')
                                                        ->label('TP Perlu Peningkatan')
                                                        ->options(function (\Filament\Schemas\Components\Utilities\Get $get) {
                                                            $subjectId = $get('../../subject_id');
                                                            $studyGroupId = $get('../../study_group_id');
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
                                                                ->toArray();
                                                        })
                                                        ->live()
                                                        ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                                            $optimal = $get('optimal_tp_ids') ?? [];
                                                            $filteredOptimal = array_values(array_diff($optimal, $state ?? []));
                                                            $set('optimal_tp_ids', $filteredOptimal);
                                                        })
                                                        ->bulkToggleable(),
                                                ])
                                                ->columnSpan(8),
                                        ]),
                                ])
                                ->addable(false)
                                ->deletable(false)
                                ->reorderable(false),
                        ])
                        ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => filled($get('study_group_id'))),
                ])
                ->action(function (array $data): void {
                    $academicYearId = \App\Models\AcademicYear::where('is_active', true)->first()?->id;
                    $teacherId = auth()->user()->teacher?->id;

                    foreach ($data['items'] as $item) {
                        \App\Models\Grade::updateOrCreate(
                            [
                                'student_id' => $item['student_id'],
                                'subject_id' => $data['subject_id'],
                                'study_group_id' => $data['study_group_id'],
                                'academic_year_id' => $academicYearId,
                            ],
                            [
                                'teacher_id' => $teacherId,
                                'nilai_tugas' => $item['nilai_tugas'] ?? 0,
                                'nilai_uts' => $item['nilai_uts'] ?? 0,
                                'nilai_uas' => $item['nilai_uas'] ?? 0,
                                'optimal_tp_ids' => $item['optimal_tp_ids'] ?? [],
                                'improved_tp_ids' => $item['improved_tp_ids'] ?? [],
                            ]
                        );
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Berhasil menyimpan nilai batch')
                        ->success()
                        ->send();
                }),
            CreateAction::make()
                ->modalWidth('7xl')
                ->closeModalByClickingAway(false),
        ];
    }

    public static function loadStudentsForGrading($get, $set): void
    {
        $studyGroupId = $get('study_group_id');
        $subjectId = $get('subject_id');
        
        if (!$studyGroupId || !$subjectId) {
            $set('items', []);
            return;
        }

        $academicYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');

        $students = \App\Models\Student::whereHas('studyGroups', fn ($q) => $q->where('study_groups.id', $studyGroupId))
            ->with('user')
            ->get();

        $existing = \App\Models\Grade::where('study_group_id', $studyGroupId)
            ->where('subject_id', $subjectId)
            ->where('academic_year_id', $academicYearId)
            ->get()
            ->keyBy('student_id');

        $items = $students->map(fn ($student) => [
            'student_id' => $student->id,
            'student_name' => $student->user?->name ?? 'Unknown',
            'nilai_tugas' => $existing[$student->id]->nilai_tugas ?? null,
            'nilai_uts' => $existing[$student->id]->nilai_uts ?? null,
            'nilai_uas' => $existing[$student->id]->nilai_uas ?? null,
            'optimal_tp_ids' => $existing[$student->id]->optimal_tp_ids ?? [],
            'improved_tp_ids' => $existing[$student->id]->improved_tp_ids ?? [],
        ])->toArray();

        $set('items', $items);
    }
}
