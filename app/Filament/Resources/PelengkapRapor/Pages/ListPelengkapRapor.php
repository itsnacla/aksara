<?php

namespace App\Filament\Resources\PelengkapRapor\Pages;

use App\Filament\Resources\PelengkapRapor\PelengkapRaporResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use App\Models\StudyGroup;
use App\Models\Student;

class ListPelengkapRapor extends ListRecords
{
    protected static string $resource = PelengkapRaporResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate_buku_induk_batch')
                ->label('Generate Per Batch')
                ->icon('heroicon-o-cpu-chip')
                ->color('success')
                ->modalHeading('Generate Buku Induk Per Rombongan Belajar (Batch)')
                ->modalDescription('Proses ini akan men-generate data Buku Induk / Rapor secara massal menggunakan kecerdasan buatan (AI) untuk seluruh siswa di Rombel terpilih.')
                ->modalWidth('lg')
                ->form([
                    Select::make('study_group_id')
                        ->label('Pilih Rombongan Belajar (Rombel)')
                        ->options(fn () => StudyGroup::whereHas('academicYear', fn ($q) => $q->where('is_active', true))
                            ->whereHas('level', fn ($q) => $q->where('nama_tingkatan', 'like', '%Kelas 1%'))
                            ->pluck('nama_rombel', 'id'))
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

                    $students = Student::with(['studyGroups.level'])
                        ->whereHas('studyGroups', fn ($q) => $q->where('study_groups.id', $studyGroupId))
                        ->get();
                    
                    if ($students->isEmpty()) {
                        \Filament\Notifications\Notification::make()
                            ->title('Rombel terpilih tidak memiliki siswa')
                            ->danger()
                            ->send();
                        return;
                    }

                    $bukuIndukService = new \App\Services\Academic\BukuIndukService();
                    $successCount = 0;

                    /** @var Student $student */
                    foreach ($students as $student) {
                        try {
                            $bukuIndukService->generateStudentBukuInduk($student, $activeYearId);
                            $successCount++;
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error("Batch AI Buku Induk failed for Student ID {$student->id}: " . $e->getMessage());
                        }
                    }

                    \Filament\Notifications\Notification::make()
                        ->title("Buku Induk berhasil digenerate untuk {$successCount} siswa di Rombel")
                        ->success()
                        ->send();
                }),
            Action::make('cetak_pelengkap_batch')
                ->label('Cetak Pelengkap Batch')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->modalHeading('Cetak Pelengkap Rapor Per Rombongan Belajar (Batch)')
                ->modalWidth('lg')
                ->form([
                    Select::make('study_group_id')
                        ->label('Pilih Rombongan Belajar (Rombel)')
                        ->options(fn () => StudyGroup::whereHas('academicYear', fn ($q) => $q->where('is_active', true))
                            ->whereHas('level', fn ($q) => $q->where('nama_tingkatan', 'like', '%Kelas 1%'))
                            ->pluck('nama_rombel', 'id'))
                        ->required()
                        ->searchable(),
                ])
                ->action(function (array $data, ListPelengkapRapor $livewire) {
                    $studyGroupId = $data['study_group_id'];
                    $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
                    
                    if (!$activeYearId) {
                        \Filament\Notifications\Notification::make()
                            ->title('Tahun ajaran aktif tidak ditemukan')
                            ->danger()
                            ->send();
                        return;
                    }
                    
                    $studentIds = Student::whereHas('studyGroups', fn ($q) => $q->where('study_groups.id', $studyGroupId))
                        ->pluck('id')
                        ->implode(',');
                        
                    if (empty($studentIds)) {
                        \Filament\Notifications\Notification::make()
                            ->title('Gagal Cetak Massal')
                            ->body('Rombel terpilih tidak memiliki siswa!')
                            ->danger()
                            ->send();
                        return;
                    }
                    
                    $url = route('print.pelengkap-rapor-bulk', [
                        'student_ids' => $studentIds,
                    ]);
                    
                    $livewire->js("window.open('{$url}', '_blank');");
                }),
        ];
    }
}
