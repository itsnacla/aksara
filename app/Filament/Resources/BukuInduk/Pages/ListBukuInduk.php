<?php

namespace App\Filament\Resources\BukuInduk\Pages;

use App\Filament\Resources\BukuInduk\BukuIndukResource;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\StudyGroup;
use App\Services\Academic\BukuIndukService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Log;

class ListBukuInduk extends ListRecords
{
    protected static string $resource = BukuIndukResource::class;

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
                            ->pluck('nama_rombel', 'id'))
                        ->required()
                        ->searchable(),
                ])
                ->action(function (array $data) {
                    $studyGroupId = $data['study_group_id'];
                    $activeYearId = AcademicYear::where('is_active', true)->value('id');

                    if (! $activeYearId) {
                        Notification::make()
                            ->title('Tahun ajaran aktif tidak ditemukan')
                            ->danger()
                            ->send();

                        return;
                    }

                    $students = Student::with(['studyGroups.level'])
                        ->whereHas('studyGroups', fn ($q) => $q->where('study_groups.id', $studyGroupId))
                        ->get();

                    if ($students->isEmpty()) {
                        Notification::make()
                            ->title('Rombel terpilih tidak memiliki siswa')
                            ->danger()
                            ->send();

                        return;
                    }

                    $bukuIndukService = new BukuIndukService;
                    $successCount = 0;

                    /** @var Student $student */
                    foreach ($students as $student) {
                        try {
                            $bukuIndukService->generateStudentBukuInduk($student, $activeYearId);
                            $successCount++;
                        } catch (\Exception $e) {
                            Log::error("Batch AI Buku Induk failed for Student ID {$student->id}: ".$e->getMessage());
                        }
                    }

                    Notification::make()
                        ->title("Buku Induk berhasil digenerate untuk {$successCount} siswa di Rombel")
                        ->success()
                        ->send();
                }),
            Action::make('cetak_buku_induk_batch')
                ->label('Cetak Buku Induk Batch')
                ->icon('heroicon-o-document-text')
                ->color('warning')
                ->modalHeading('Cetak Buku Induk Per Rombongan Belajar (Batch)')
                ->modalWidth('lg')
                ->form([
                    Select::make('study_group_id')
                        ->label('Pilih Rombongan Belajar (Rombel)')
                        ->options(fn () => StudyGroup::whereHas('academicYear', fn ($q) => $q->where('is_active', true))
                            ->pluck('nama_rombel', 'id'))
                        ->required()
                        ->searchable(),
                ])
                ->action(function (array $data, ListBukuInduk $livewire) {
                    $studyGroupId = $data['study_group_id'];
                    $activeYearId = AcademicYear::where('is_active', true)->value('id');

                    if (! $activeYearId) {
                        Notification::make()
                            ->title('Tahun ajaran aktif tidak ditemukan')
                            ->danger()
                            ->send();

                        return;
                    }

                    $studentIds = Student::whereHas('studyGroups', fn ($q) => $q->where('study_groups.id', $studyGroupId))
                        ->pluck('id')
                        ->implode(',');

                    if (empty($studentIds)) {
                        Notification::make()
                            ->title('Gagal Cetak Massal')
                            ->body('Rombel terpilih tidak memiliki siswa!')
                            ->danger()
                            ->send();

                        return;
                    }

                    $url = route('print.buku-induk-bulk', [
                        'student_ids' => $studentIds,
                    ]);

                    $livewire->js("window.open('{$url}', '_blank');");
                }),
        ];
    }
}
