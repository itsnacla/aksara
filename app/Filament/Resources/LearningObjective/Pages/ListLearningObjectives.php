<?php

namespace App\Filament\Resources\LearningObjective\Pages;

use App\Filament\Resources\LearningObjective\LearningObjectiveResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLearningObjectives extends ListRecords
{
    protected static string $resource = LearningObjectiveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('batch_input_tp')
                ->label('Batch Input TP')
                ->icon('heroicon-o-academic-cap')
                ->color('success')
                ->modalWidth('7xl')
                ->closeModalByClickingAway(false)
                ->form([
                    \Filament\Schemas\Components\Section::make('Filter Mata Pelajaran & Tingkatan')
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
                                            $query->where(function ($q) use ($teacherId) {
                                                $q->where('is_umum', true)
                                                  ->orWhereHas('schedules', fn ($sq) => $sq->where('teacher_id', $teacherId))
                                                  ->orWhereHas('teachers', fn ($tq) => $tq->where('teachers.id', $teacherId));
                                            });
                                        } else {
                                            $query->where(function ($q) use ($teacherId) {
                                                $q->whereHas('schedules', fn ($sq) => $sq->where('teacher_id', $teacherId))
                                                  ->orWhereHas('teachers', fn ($tq) => $tq->where('teachers.id', $teacherId));
                                            });
                                        }
                                    }
                                    return $query->pluck('nama_mapel', 'id');
                                })
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (\Filament\Schemas\Components\Utilities\Set $set) {
                                    $user = auth()->user();
                                    $isWaliKelas = $user && $user->teacher && $user->teacher->is_walikelas;
                                    
                                    if ($isWaliKelas) {
                                        $managedStudyGroup = \App\Models\StudyGroup::where('walikelas_id', $user->teacher->id)
                                            ->whereHas('academicYear', fn ($q) => $q->where('is_active', true))
                                            ->first();
                                        
                                        $set('level_id', $managedStudyGroup?->level_id);
                                    }
                                }),
                            \Filament\Forms\Components\Select::make('level_id')
                                ->label('Tingkatan / Fase')
                                ->options(function (\Filament\Schemas\Components\Utilities\Get $get) {
                                    $user = auth()->user();
                                    $isWaliKelas = $user && $user->teacher && $user->teacher->is_walikelas;
                                    
                                    if ($isWaliKelas) {
                                        // For wali kelas, always return their level regardless of subject_id
                                        $managedStudyGroup = \App\Models\StudyGroup::where('walikelas_id', $user->teacher->id)
                                            ->whereHas('academicYear', fn ($q) => $q->where('is_active', true))
                                            ->first();
                                        
                                        if ($managedStudyGroup && $managedStudyGroup->level) {
                                            return [$managedStudyGroup->level->id => $managedStudyGroup->level->nama_tingkatan];
                                        }
                                        return [];
                                    }
                                    
                                    // For guru mapel, require subject_id to be selected first
                                    $subjectId = $get('subject_id');
                                    if (!$subjectId) {
                                        return [];
                                    }
                                    
                                    $subject = \App\Models\Subject::find($subjectId);
                                    if ($subject) {
                                        return $subject->levels->pluck('nama_tingkatan', 'id')->toArray();
                                    }
                                    return [];
                                })
                                ->default(function (\Filament\Schemas\Components\Utilities\Get $get) {
                                    $subjectId = $get('subject_id');
                                    if (!$subjectId) {
                                        return null;
                                    }
                                    $user = auth()->user();
                                    $isWaliKelas = $user && $user->teacher && $user->teacher->is_walikelas;
                                    
                                    if ($isWaliKelas) {
                                        $managedStudyGroup = \App\Models\StudyGroup::where('walikelas_id', $user->teacher->id)
                                            ->whereHas('academicYear', fn ($q) => $q->where('is_active', true))
                                            ->first();
                                        
                                        return $managedStudyGroup?->level_id;
                                    }
                                    return null;
                                })
                                ->disabled(function () {
                                    $user = auth()->user();
                                    return $user && $user->teacher && $user->teacher->is_walikelas;
                                })
                                ->dehydrated()
                                ->live()
                                ->required(),
                        ])->columns(2),
                    
                    \Filament\Schemas\Components\Section::make('Daftar TP')
                        ->schema([
                            \Filament\Forms\Components\Repeater::make('items')
                                ->label('')
                                ->minItems(5)
                                ->defaultItems(5)
                                ->schema([
                                    \Filament\Forms\Components\TextInput::make('code')
                                        ->label('Kode TP')
                                        ->placeholder('Contoh: TP 1.1')
                                        ->required(),
                                    \Filament\Forms\Components\Textarea::make('description')
                                        ->label('Deskripsi TP')
                                        ->placeholder('Contoh: Menjelaskan proses fotosintesis...')
                                        ->maxLength(200)
                                        ->required()
                                        ->rows(2),
                                    \Filament\Forms\Components\Toggle::make('is_active')
                                        ->label('Aktif')
                                        ->default(true),
                                ])->columns(1),
                        ]),
                ])
                ->action(function (array $data) {
                    $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
                    
                    foreach ($data['items'] as $item) {
                        \App\Models\LearningObjective::create([
                            'subject_id' => $data['subject_id'],
                            'level_id' => $data['level_id'],
                            'academic_year_id' => $activeYearId,
                            'code' => $item['code'],
                            'description' => $item['description'],
                            'is_active' => $item['is_active'] ?? true,
                        ]);
                    }
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Berhasil')
                        ->body('TP berhasil dibuat!')
                        ->success()
                        ->send();
                }),
            
            Actions\CreateAction::make()->modalWidth('5xl'),
        ];
    }

    public function getFooter(): ?\Illuminate\Contracts\View\View
    {
        return view('filament.pages.scroll-to-top-script');
    }
}
