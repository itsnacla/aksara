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
                ->modalWidth('5xl')
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
                                    \Filament\Forms\Components\TextInput::make('student_name')
                                        ->label('Siswa')
                                        ->disabled()
                                        ->dehydrated(false),
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
                                ])
                                ->columns(5)
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
                            ]
                        );
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Berhasil menyimpan nilai batch')
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
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
        ])->toArray();

        $set('items', $items);
    }
}
