<?php

namespace App\Filament\Resources\RaporResource\Pages;

use App\Filament\Resources\RaporResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use App\Models\StudyGroup;
use App\Models\Student;

class ListRapors extends ListRecords
{
    protected static string $resource = RaporResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate_rapor_batch')
                ->label('Generate Per Batch')
                ->icon('heroicon-o-cpu-chip')
                ->color('success')
                ->modalHeading('Generate Rapor Per Rombongan Belajar (Batch) via AI')
                ->modalDescription('Proses ini akan men-generate rapor secara massal menggunakan kecerdasan buatan (AI) untuk menganalisis nilai & absensi seluruh siswa di Rombel terpilih.')
                ->modalWidth('lg')
                ->form([
                    Select::make('study_group_id')
                        ->label('Pilih Rombongan Belajar (Rombel)')
                        ->options(fn () => StudyGroup::whereHas('academicYear', fn ($q) => $q->where('is_active', true))->pluck('nama_rombel', 'id'))
                        ->required()
                        ->searchable(),
                ])
                ->action(function (array $data) {
                    $studyGroupId = $data['study_group_id'];
                    $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
                    
                    if (!$activeYearId) {
                        \Filament\Notifications\Notification::make()
                            ->title('Tahun ajaran aktif tidak ditemukan')
                            ->danger()
                            ->send();
                        return;
                    }

                    $students = Student::whereHas('studyGroups', fn ($q) => $q->where('study_groups.id', $studyGroupId))->get();
                    
                    if ($students->isEmpty()) {
                        \Filament\Notifications\Notification::make()
                            ->title('Rombel terpilih tidak memiliki siswa')
                            ->danger()
                            ->send();
                        return;
                    }

                    $raporService = new \App\Services\Academic\RaporService();
                    $successCount = 0;

                    foreach ($students as $student) {
                        try {
                            $raporService->generateStudentRapor($student, $activeYearId);
                            $successCount++;
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error("Batch AI Rapor failed for Student ID {$student->id}: " . $e->getMessage());
                        }
                    }

                    \Filament\Notifications\Notification::make()
                        ->title("Rapor berhasil digenerate untuk {$successCount} siswa di Rombel")
                        ->success()
                        ->send();
                }),
            Action::make('cetak_rapor_batch')
                ->label('Cetak Per Batch')
                ->icon('heroicon-o-printer')
                ->color('warning')
                ->modalHeading('Cetak Rapor Per Rombongan Belajar (Batch)')
                ->modalWidth('lg')
                ->form([
                    Select::make('study_group_id')
                        ->label('Pilih Rombongan Belajar (Rombel)')
                        ->options(fn () => StudyGroup::whereHas('academicYear', fn ($q) => $q->where('is_active', true))->pluck('nama_rombel', 'id'))
                        ->required()
                        ->searchable(),
                    Select::make('paper_size')
                        ->label('Ukuran Kertas')
                        ->options([
                            'a4' => 'A4 (210 x 297 mm)',
                            'f4' => 'F4 / Folio (215 x 330 mm)',
                        ])
                        ->default('a4')
                        ->required(),
                    Select::make('margin_size')
                        ->label('Margin Halaman')
                        ->options([
                            'normal' => 'Normal (10mm)',
                            'sedang' => 'Sedang (7mm)',
                            'sempit' => 'Sempit (5mm)',
                            'none' => 'Tanpa Margin (0mm)',
                        ])
                        ->default('normal')
                        ->required(),
                ])
                ->action(function (array $data, ListRapors $livewire) {
                    $studyGroupId = $data['study_group_id'];
                    $paperSize = $data['paper_size'];
                    $marginSize = $data['margin_size'];
                    
                    $studentIds = Student::whereHas('studyGroups', fn ($q) => $q->where('study_groups.id', $studyGroupId))
                        ->pluck('id')
                        ->implode(',');
                        
                    if (empty($studentIds)) {
                        \Filament\Notifications\Notification::make()
                            ->title('Rombel terpilih tidak memiliki siswa')
                            ->danger()
                            ->send();
                        return;
                    }
                    
                    $url = route('print.rapor.bulk', [
                        'student_ids' => $studentIds,
                        'paper_size' => $paperSize,
                        'margin_size' => $marginSize,
                    ]);
                    
                    $livewire->js("window.open('{$url}', '_blank');");
                })
        ];
    }
}
