<?php

namespace App\Filament\Resources\Rapor\Pages;

use App\Filament\Resources\Rapor\RaporResource;
use App\Models\AcademicYear;
use App\Models\ExtracurricularGrade;
use App\Models\Grade;
use App\Models\Student;
use App\Models\StudentRapor;
use App\Models\StudyGroup;
use App\Services\Academic\RaporService;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

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
                        ->options(function () {
                            $query = StudyGroup::whereHas('academicYear', fn ($q) => $q->where('is_active', true));
                            $user = auth()->user();
                            if ($user && ! $user->hasAnyRole(['super_admin', 'staff'])) {
                                $query->where('walikelas_id', $user->teacher?->id ?? 0);
                            }

                            return $query->pluck('nama_rombel', 'id');
                        })
                        ->required()
                        ->searchable()
                        ->live(),
                    Placeholder::make('grade_warning')
                        ->hiddenLabel()
                        ->content(function (Get $get) {
                            $studyGroupId = $get('study_group_id');
                            if (! $studyGroupId) {
                                return null;
                            }

                            $activeYearId = AcademicYear::where('is_active', true)->value('id');
                            if (! $activeYearId) {
                                return null;
                            }

                            $studentIds = Student::whereHas('studyGroups', fn ($q) => $q->where('study_groups.id', $studyGroupId))->pluck('id');
                            if ($studentIds->isEmpty()) {
                                return null;
                            }

                            $hasRegularGrades = Grade::whereIn('student_id', $studentIds)
                                ->where('study_group_id', $studyGroupId)
                                ->where('academic_year_id', $activeYearId)
                                ->exists();
                            $hasEkskulGrades = ExtracurricularGrade::whereIn('student_id', $studentIds)
                                ->where('academic_year_id', $activeYearId)
                                ->exists();

                            if ($hasRegularGrades && $hasEkskulGrades) {
                                return null;
                            }

                            $missing = [];
                            if (! $hasRegularGrades) {
                                $missing[] = 'Nilai Akademik';
                            }
                            if (! $hasEkskulGrades) {
                                $missing[] = 'Nilai Ekstrakurikuler';
                            }
                            $missingText = implode(' & ', $missing);

                            return new HtmlString(
                                '<div class="rounded-lg border border-danger-300 bg-danger-50 dark:bg-danger-950/30 dark:border-danger-800 p-4">'.
                                '<div class="flex items-start gap-3">'.
                                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="20" height="20" style="flex-shrink:0;color:#dc2626;margin-top:2px;"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>'.
                                '<div class="text-sm text-danger-700 dark:text-danger-300">'.
                                '<p class="font-semibold">Belum bisa generate rapor</p>'.
                                '<p class="mt-1"><strong>'.e($missingText).'</strong> belum diinput untuk siswa di rombel ini. Silakan lengkapi penilaian terlebih dahulu sebelum melakukan generate rapor.</p>'.
                                '</div></div></div>'
                            );
                        })
                        ->visible(function (Get $get) {
                            $studyGroupId = $get('study_group_id');
                            if (! $studyGroupId) {
                                return false;
                            }

                            $activeYearId = AcademicYear::where('is_active', true)->value('id');
                            if (! $activeYearId) {
                                return false;
                            }

                            $studentIds = Student::whereHas('studyGroups', fn ($q) => $q->where('study_groups.id', $studyGroupId))->pluck('id');
                            if ($studentIds->isEmpty()) {
                                return false;
                            }

                            $hasRegularGrades = Grade::whereIn('student_id', $studentIds)
                                ->where('study_group_id', $studyGroupId)
                                ->where('academic_year_id', $activeYearId)
                                ->exists();
                            $hasEkskulGrades = ExtracurricularGrade::whereIn('student_id', $studentIds)
                                ->where('academic_year_id', $activeYearId)
                                ->exists();

                            return ! $hasRegularGrades || ! $hasEkskulGrades;
                        }),
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

                    $studyGroup = StudyGroup::with('level.subjects')->find($studyGroupId);
                    if (! $studyGroup || ! $studyGroup->level) {
                        Notification::make()
                            ->title('Rombel atau tingkat kelas tidak ditemukan')
                            ->danger()
                            ->send();

                        return;
                    }

                    $user = auth()->user();
                    if ($user && ! $user->hasAnyRole(['super_admin', 'staff'])) {
                        if ($studyGroup->walikelas_id !== ($user->teacher?->id ?? 0)) {
                            Notification::make()
                                ->title('Tidak diizinkan')
                                ->body('Hanya wali kelas dari rombel ini yang bisa generate rapor.')
                                ->danger()
                                ->send();

                            return;
                        }
                    }

                    $students = Student::whereHas('studyGroups', fn ($q) => $q->where('study_groups.id', $studyGroupId))->with('user')->get();

                    if ($students->isEmpty()) {
                        Notification::make()
                            ->title('Rombel terpilih tidak memiliki siswa')
                            ->danger()
                            ->send();

                        return;
                    }

                    // Validasi awal: pastikan minimal ada input nilai akademik & ekskul
                    $studentIds = $students->pluck('id');
                    $hasRegularGrades = Grade::whereIn('student_id', $studentIds)
                        ->where('study_group_id', $studyGroupId)
                        ->where('academic_year_id', $activeYearId)
                        ->exists();
                    $hasEkskulGrades = ExtracurricularGrade::whereIn('student_id', $studentIds)
                        ->where('academic_year_id', $activeYearId)
                        ->exists();

                    if (! $hasRegularGrades || ! $hasEkskulGrades) {
                        $missing = [];
                        if (! $hasRegularGrades) {
                            $missing[] = 'Nilai Akademik';
                        }
                        if (! $hasEkskulGrades) {
                            $missing[] = 'Nilai Ekstrakurikuler';
                        }

                        Notification::make()
                            ->title('Gagal Generate Rapor')
                            ->body(implode(' & ', $missing).' belum diinput untuk siswa di rombel ini. Silakan lengkapi penilaian terlebih dahulu.')
                            ->danger()
                            ->persistent()
                            ->send();

                        return;
                    }

                    // Validasi: Cek apakah semua siswa sudah punya nilai lengkap
                    $subjects = $studyGroup->level->subjects()->where('is_graded', true)->get();
                    if ($subjects->isEmpty()) {
                        Notification::make()
                            ->title('Tidak ada mata pelajaran yang dinilai untuk tingkat kelas ini')
                            ->danger()
                            ->send();

                        return;
                    }

                    $incompleteData = [];
                    foreach ($students as $student) {
                        $missingSubjects = [];
                        foreach ($subjects as $subject) {
                            $gradeExists = Grade::where('student_id', $student->id)
                                ->where('subject_id', $subject->id)
                                ->where('academic_year_id', $activeYearId)
                                ->exists();
                            if (! $gradeExists) {
                                $missingSubjects[] = $subject->nama_mapel;
                            }
                        }
                        if (! empty($missingSubjects)) {
                            $incompleteData[] = [
                                'student' => $student->user->name,
                                'subjects' => $missingSubjects,
                            ];
                        }
                    }

                    if (! empty($incompleteData)) {
                        $errorDetails = [];
                        foreach ($incompleteData as $data) {
                            $errorDetails[] = "• {$data['student']}: ".implode(', ', $data['subjects']);
                        }
                        $errorMessage = "Nilai belum lengkap untuk siswa berikut:\n\n".implode("\n", $errorDetails)."\n\nSilakan lengkapi nilai terlebih dahulu sebelum melakukan generate rapor!";

                        Notification::make()
                            ->title('Gagal Generate Rapor - Nilai Belum Lengkap')
                            ->body($errorMessage)
                            ->danger()
                            ->persistent()
                            ->send();

                        return;
                    }

                    // Jika validasi lolos, lanjutkan generate rapor
                    $raporService = new RaporService;
                    $successCount = 0;

                    /** @var Student $student */
                    foreach ($students as $student) {
                        try {
                            $raporService->generateStudentRapor($student, $activeYearId);
                            $successCount++;
                        } catch (\Exception $e) {
                            Log::error("Batch AI Rapor failed for Student ID {$student->id}: ".$e->getMessage());
                        }
                    }

                    Notification::make()
                        ->title("Rapor berhasil digenerate untuk {$successCount} siswa di Rombel")
                        ->success()
                        ->send();
                }),
            Action::make('publish_rapor_batch')
                ->label('Tampilkan Per Batch')
                ->icon('heroicon-o-eye')
                ->color('success')
                ->modalHeading('Tampilkan Rapor Ke Orang Tua & Siswa (Batch)')
                ->modalDescription('Proses ini akan mempublikasikan Rapor seluruh siswa di Rombel terpilih sehingga dapat diakses oleh orang tua dan siswa di portal masing-masing.')
                ->modalWidth('lg')
                ->form([
                    Select::make('study_group_id')
                        ->label('Pilih Rombongan Belajar (Rombel)')
                        ->options(function () {
                            $query = StudyGroup::whereHas('academicYear', fn ($q) => $q->where('is_active', true));
                            $user = auth()->user();
                            if ($user && ! $user->hasAnyRole(['super_admin', 'staff'])) {
                                $query->where('walikelas_id', $user->teacher?->id ?? 0);
                            }

                            return $query->pluck('nama_rombel', 'id');
                        })
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

                    $user = auth()->user();
                    if ($user && ! $user->hasAnyRole(['super_admin', 'staff'])) {
                        $studyGroup = StudyGroup::find($studyGroupId);
                        if (! $studyGroup || $studyGroup->walikelas_id !== ($user->teacher?->id ?? 0)) {
                            Notification::make()
                                ->title('Tidak diizinkan')
                                ->body('Hanya wali kelas dari rombel ini yang bisa menampilkan rapor.')
                                ->danger()
                                ->send();

                            return;
                        }
                    }

                    $students = Student::whereHas('studyGroups', fn ($q) => $q->where('study_groups.id', $studyGroupId))->get();

                    if ($students->isEmpty()) {
                        Notification::make()
                            ->title('Rombel terpilih tidak memiliki siswa')
                            ->danger()
                            ->send();

                        return;
                    }

                    $count = 0;
                    foreach ($students as $student) {
                        $rapor = StudentRapor::where('student_id', $student->id)
                            ->where('academic_year_id', $activeYearId)
                            ->first();
                        if ($rapor) {
                            $rapor->update(['is_published' => true]);
                            $count++;
                        }
                    }

                    Notification::make()
                        ->title("Rapor berhasil dipublikasikan untuk {$count} siswa di Rombel!")
                        ->success()
                        ->send();
                }),
            Action::make('unpublish_rapor_batch')
                ->label('Sembunyikan Per Batch')
                ->icon('heroicon-o-eye-slash')
                ->color('danger')
                ->modalHeading('Tarik Rapor Dari Orang Tua & Siswa (Batch)')
                ->modalDescription('Proses ini akan menarik kembali publikasi Rapor sehingga disembunyikan dari akses orang tua dan siswa di portal masing-masing.')
                ->modalWidth('lg')
                ->form([
                    Select::make('study_group_id')
                        ->label('Pilih Rombongan Belajar (Rombel)')
                        ->options(function () {
                            $query = StudyGroup::whereHas('academicYear', fn ($q) => $q->where('is_active', true));
                            $user = auth()->user();
                            if ($user && ! $user->hasAnyRole(['super_admin', 'staff'])) {
                                $query->where('walikelas_id', $user->teacher?->id ?? 0);
                            }

                            return $query->pluck('nama_rombel', 'id');
                        })
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

                    $user = auth()->user();
                    if ($user && ! $user->hasAnyRole(['super_admin', 'staff'])) {
                        $studyGroup = StudyGroup::find($studyGroupId);
                        if (! $studyGroup || $studyGroup->walikelas_id !== ($user->teacher?->id ?? 0)) {
                            Notification::make()
                                ->title('Tidak diizinkan')
                                ->body('Hanya wali kelas dari rombel ini yang bisa menyembunyikan rapor.')
                                ->danger()
                                ->send();

                            return;
                        }
                    }

                    $students = Student::whereHas('studyGroups', fn ($q) => $q->where('study_groups.id', $studyGroupId))->get();

                    if ($students->isEmpty()) {
                        Notification::make()
                            ->title('Rombel terpilih tidak memiliki siswa')
                            ->danger()
                            ->send();

                        return;
                    }

                    $count = 0;
                    foreach ($students as $student) {
                        $rapor = StudentRapor::where('student_id', $student->id)
                            ->where('academic_year_id', $activeYearId)
                            ->first();
                        if ($rapor) {
                            $rapor->update(['is_published' => false]);
                            $count++;
                        }
                    }

                    Notification::make()
                        ->title("Publikasi Rapor berhasil ditarik kembali untuk {$count} siswa di Rombel!")
                        ->warning()
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
                        ->options(function () {
                            $query = StudyGroup::whereHas('academicYear', fn ($q) => $q->where('is_active', true));
                            $user = auth()->user();
                            if ($user && ! $user->hasAnyRole(['super_admin', 'staff'])) {
                                $query->where('walikelas_id', $user->teacher?->id ?? 0);
                            }

                            return $query->pluck('nama_rombel', 'id');
                        })
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

                    $user = auth()->user();
                    if ($user && ! $user->hasAnyRole(['super_admin', 'staff'])) {
                        $studyGroup = StudyGroup::find($studyGroupId);
                        if (! $studyGroup || $studyGroup->walikelas_id !== ($user->teacher?->id ?? 0)) {
                            Notification::make()
                                ->title('Tidak diizinkan')
                                ->body('Hanya wali kelas dari rombel ini yang bisa mencetak rapor.')
                                ->danger()
                                ->send();

                            return;
                        }
                    }

                    $studentIds = Student::whereHas('studyGroups', fn ($q) => $q->where('study_groups.id', $studyGroupId))
                        ->pluck('id')
                        ->implode(',');

                    if (empty($studentIds)) {
                        Notification::make()
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
                }),
        ];
    }
}
