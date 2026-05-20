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
                                    $query = \App\Models\Subject::query()->where('is_graded', true);
                                    if ($user->hasRole('guru') && $user->teacher) {
                                        $teacherId = $user->teacher->id;
                                        $isWaliKelas = $user->teacher->is_walikelas;
                                        
                                        if ($isWaliKelas) {
                                            // Wali kelas can see: is_umum subjects OR subjects from schedules OR subjects from teacher relationship
                                            $query->where(function ($q) use ($teacherId) {
                                                $q->where('is_umum', true)
                                                  ->orWhereHas('schedules', fn ($sq) => $sq->where('teacher_id', $teacherId))
                                                  ->orWhereHas('teachers', fn ($tq) => $tq->where('teachers.id', $teacherId));
                                            });
                                        } else {
                                            // Guru mapel can see: subjects from schedules OR subjects from teacher relationship
                                            $query->where(function ($q) use ($teacherId) {
                                                $q->whereHas('schedules', fn ($sq) => $sq->where('teacher_id', $teacherId))
                                                  ->orWhereHas('teachers', fn ($tq) => $tq->where('teachers.id', $teacherId));
                                            });
                                        }
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
                                    
                                    // Get active academic year ID (automatic)
                                    $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
                                    if (!$activeYearId) return [];
                                    
                                    // Base query: ALWAYS filter by active academic year
                                    $query = \App\Models\StudyGroup::query()
                                        ->where('academic_year_id', $activeYearId);
                                    
                                    if ($user->hasRole('guru') && $user->teacher) {
                                        $teacherId = $user->teacher->id;
                                        $isWaliKelas = $user->teacher->is_walikelas;
                                        
                                        if ($isWaliKelas) {
                                            // Ambil subject untuk cek apakah is_umum
                                            $subject = \App\Models\Subject::find($subjectId);
                                            
                                            if ($subject && $subject->is_umum) {
                                                // Untuk mapel is_umum, wali kelas bisa lihat kelas yang mereka kelola
                                                $query->where('walikelas_id', $teacherId);
                                            } else {
                                                // Untuk mapel non-is_umum, hanya lihat kelas dari jadwal mereka
                                                $query->whereHas('schedules', fn ($q) => $q->where('teacher_id', $teacherId)->where('subject_id', $subjectId));
                                            }
                                        } else {
                                            // Guru mapel: cek apakah punya schedules untuk subject ini
                                            $hasSchedules = \App\Models\Schedule::where('teacher_id', $teacherId)
                                                ->where('subject_id', $subjectId)
                                                ->exists();
                                            
                                            if ($hasSchedules) {
                                                // Jika punya schedules, hanya lihat kelas dari jadwal mereka
                                                $query->whereHas('schedules', fn ($q) => $q->where('teacher_id', $teacherId)->where('subject_id', $subjectId));
                                            }
                                            // Jika tidak punya schedules, tampilkan semua rombel di tahun ajaran aktif (sudah di-filter di base query)
                                        }
                                    }
                                    // Untuk super_admin/staff: tampilkan semua rombel di tahun ajaran aktif (sudah di-filter di base query)
                                    
                                    return $query->pluck('nama_rombel', 'id');
                                })
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn (\Filament\Schemas\Components\Utilities\Get $get, \Filament\Schemas\Components\Utilities\Set $set) => self::loadStudentsForGrading($get, $set)),
                        ])->columns(2),
                    
                    \Filament\Schemas\Components\Section::make()
                        ->schema([
                            \Filament\Forms\Components\Placeholder::make('tp_warning')
                                ->hiddenLabel()
                                ->content(new \Illuminate\Support\HtmlString(
                                    '<div class="rounded-lg border border-danger-300 bg-danger-50 dark:bg-danger-950/30 dark:border-danger-800 p-4">' .
                                    '<div class="flex items-start gap-3">' .
                                    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="20" height="20" style="flex-shrink:0;color:#dc2626;margin-top:2px;"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>' .
                                    '<div class="text-sm text-danger-700 dark:text-danger-300">' .
                                    '<p class="font-semibold">Tujuan Pembelajaran (TP) belum tersedia</p>' .
                                    '<p class="mt-1">Belum ada TP aktif untuk mata pelajaran ini di tingkat kelas tersebut. Silakan tambahkan TP terlebih dahulu di menu <strong>Tujuan Pembelajaran</strong> sebelum melakukan input nilai.</p>' .
                                    '</div></div></div>'
                                )),
                        ])
                        ->visible(function (\Filament\Schemas\Components\Utilities\Get $get) {
                            $subjectId = $get('subject_id');
                            $studyGroupId = $get('study_group_id');
                            if (!$subjectId || !$studyGroupId) return false;
                            $studyGroup = \App\Models\StudyGroup::find($studyGroupId);
                            if (!$studyGroup) return false;
                            return !\App\Models\LearningObjective::where('subject_id', $subjectId)
                                ->where('level_id', $studyGroup->level_id)
                                ->where('is_active', true)
                                ->exists();
                        }),

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
                        ->visible(function (\Filament\Schemas\Components\Utilities\Get $get) {
                            $subjectId = $get('subject_id');
                            $studyGroupId = $get('study_group_id');
                            if (!$subjectId || !$studyGroupId) return false;
                            $studyGroup = \App\Models\StudyGroup::find($studyGroupId);
                            if (!$studyGroup) return false;
                            return \App\Models\LearningObjective::where('subject_id', $subjectId)
                                ->where('level_id', $studyGroup->level_id)
                                ->where('is_active', true)
                                ->exists();
                        }),
                ])
                ->action(function (array $data): void {
                    $academicYearId = \App\Models\AcademicYear::where('is_active', true)->first()?->id;
                    $teacherId = auth()->user()->teacher?->id;

                    $studyGroup = \App\Models\StudyGroup::find($data['study_group_id']);
                    $hasTp = $studyGroup && \App\Models\LearningObjective::where('subject_id', $data['subject_id'])
                        ->where('level_id', $studyGroup->level_id)
                        ->where('is_active', true)
                        ->exists();

                    if (!$hasTp) {
                        \Filament\Notifications\Notification::make()
                            ->title('Gagal menyimpan nilai')
                            ->body('Tujuan Pembelajaran (TP) belum tersedia untuk mata pelajaran ini di tingkat kelas tersebut. Silakan tambahkan TP terlebih dahulu.')
                            ->danger()
                            ->send();
                        return;
                    }

                    if (empty($data['items'])) {
                        \Filament\Notifications\Notification::make()
                            ->title('Tidak ada data nilai untuk disimpan')
                            ->warning()
                            ->send();
                        return;
                    }

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
