<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Attendances\AttendanceResource;
use App\Filament\Resources\ExtracurricularGrade\ExtracurricularGradeResource;
use App\Filament\Resources\Grades\GradeResource;
use App\Filament\Resources\Rapor\RaporResource;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Extracurricular;
use App\Models\ExtracurricularGrade;
use App\Models\Grade;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\StudentRapor;
use App\Models\StudyGroup;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class DataProgressTableWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Breakdown Progress per Rombel';

    public function table(Table $table): Table
    {
        $activeYear = AcademicYear::where('is_active', true)->first();

        $query = StudyGroup::query();
        if ($activeYear) {
            $query->where('academic_year_id', $activeYear->id);

            if (auth()->user() && auth()->user()->hasRole('guru')) {
                $teacherId = auth()->user()->teacher?->id;
                if ($teacherId) {
                    $query->where(function ($q) use ($teacherId) {
                        $q->where('walikelas_id', $teacherId)
                            ->orWhereHas('schedules', fn ($sq) => $sq->where('teacher_id', $teacherId));
                    });
                } else {
                    $query->where('id', 0);
                }
            }
        } else {
            $query->where('id', 0); // Return empty if no active year
        }

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('nama_rombel')
                    ->label('Nama Rombel')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('waliKelas.nama_lengkap')
                    ->label('Wali Kelas')
                    ->searchable(false)
                    ->sortable(false)
                    ->default('-'),

                Tables\Columns\TextColumn::make('progress_nilai')
                    ->label('Progress Nilai')
                    ->getStateUsing(function (StudyGroup $record) use ($activeYear) {
                        if (! $activeYear) {
                            return '0 / 0 (0%)';
                        }

                        $studentCount = $record->students()->count();
                        $scheduleCount = $record->schedules()
                            ->whereHas('subject', function ($sq) {
                                $sq->where('is_graded', true);
                            })
                            ->distinct('subject_id')
                            ->count('subject_id');
                        $expected = $studentCount * $scheduleCount;

                        $current = Grade::where('academic_year_id', $activeYear->id)
                            ->where('study_group_id', $record->id)
                            ->count();

                        $percent = $expected > 0 ? round(($current / $expected) * 100, 1) : 0;
                        if ($percent > 100) {
                            $percent = 100;
                        }

                        return "{$current} / {$expected} ({$percent}%)";
                    })
                    ->badge()
                    ->color(fn (string $state): string => str_contains($state, '(100%)') ? 'success' : (str_contains($state, '(0%)') ? 'danger' : 'warning')),

                Tables\Columns\TextColumn::make('progress_rapor')
                    ->label('Progress Cetak Rapor')
                    ->getStateUsing(function (StudyGroup $record) use ($activeYear) {
                        if (! $activeYear) {
                            return '0 / 0 (0%)';
                        }

                        $studentIds = $record->students()->pluck('students.id');
                        $expected = $studentIds->count();

                        $current = StudentRapor::where('academic_year_id', $activeYear->id)
                            ->whereIn('student_id', $studentIds)
                            ->count();

                        $percent = $expected > 0 ? round(($current / $expected) * 100, 1) : 0;
                        if ($percent > 100) {
                            $percent = 100;
                        }

                        return "{$current} / {$expected} ({$percent}%)";
                    })
                    ->badge()
                    ->color(fn (string $state): string => str_contains($state, '(100%)') ? 'success' : (str_contains($state, '(0%)') ? 'danger' : 'primary')),

                Tables\Columns\TextColumn::make('progress_presensi')
                    ->label('Progress Presensi (Hari Ini)')
                    ->getStateUsing(function (StudyGroup $record) {
                        $studentIds = $record->students()->pluck('students.id');
                        $expected = $studentIds->count();

                        $current = Attendance::whereIn('student_id', $studentIds)
                            ->where('tanggal', now()->toDateString())
                            ->count();

                        $percent = $expected > 0 ? round(($current / $expected) * 100, 1) : 0;
                        if ($percent > 100) {
                            $percent = 100;
                        }

                        return "{$current} / {$expected} ({$percent}%)";
                    })
                    ->badge()
                    ->color(fn (string $state): string => str_contains($state, '(100%)') ? 'success' : (str_contains($state, '(0%)') ? 'danger' : 'warning')),

                Tables\Columns\TextColumn::make('progress_ekskul')
                    ->label('Progress Ekskul')
                    ->getStateUsing(function (StudyGroup $record) use ($activeYear) {
                        if (! $activeYear) {
                            return '0 / 0 (0%)';
                        }

                        $studentIds = $record->students()->pluck('students.id');

                        $expected = DB::table('extracurricular_student')
                            ->whereIn('student_id', $studentIds)
                            ->count();

                        $current = ExtracurricularGrade::where('academic_year_id', $activeYear->id)
                            ->whereIn('student_id', $studentIds)
                            ->whereExists(function ($q) {
                                $q->selectRaw('1')
                                    ->from('extracurricular_student')
                                    ->whereColumn('extracurricular_student.student_id', 'extracurricular_grades.student_id')
                                    ->whereColumn('extracurricular_student.extracurricular_id', 'extracurricular_grades.extracurricular_id');
                            })
                            ->count();

                        $percent = $expected > 0 ? round(($current / $expected) * 100, 1) : 0;
                        if ($percent > 100) {
                            $percent = 100;
                        }

                        return "{$current} / {$expected} ({$percent}%)";
                    })
                    ->badge()
                    ->color(fn (string $state): string => str_contains($state, '(100%)') ? 'success' : (str_contains($state, '(0%)') ? 'danger' : 'warning')),

                Tables\Columns\TextColumn::make('progress_catatan_wali')
                    ->label('Catatan Wali')
                    ->getStateUsing(function (StudyGroup $record) use ($activeYear) {
                        if (! $activeYear) {
                            return '0 / 0 (0%)';
                        }

                        $studentIds = $record->students()->pluck('students.id');
                        $expected = $studentIds->count();

                        $current = StudentRapor::where('academic_year_id', $activeYear->id)
                            ->whereIn('student_id', $studentIds)
                            ->whereNotNull('catatan_wali_kelas')
                            ->where('catatan_wali_kelas', '!=', '')
                            ->count();

                        $percent = $expected > 0 ? round(($current / $expected) * 100, 1) : 0;
                        if ($percent > 100) {
                            $percent = 100;
                        }

                        return "{$current} / {$expected} ({$percent}%)";
                    })
                    ->badge()
                    ->color(fn (string $state): string => str_contains($state, '(100%)') ? 'success' : (str_contains($state, '(0%)') ? 'danger' : 'warning')),

                Tables\Columns\TextColumn::make('progress_publikasi')
                    ->label('Publikasi Rapor')
                    ->getStateUsing(function (StudyGroup $record) use ($activeYear) {
                        if (! $activeYear) {
                            return '0 / 0 (0%)';
                        }

                        $studentIds = $record->students()->pluck('students.id');
                        $expected = $studentIds->count();

                        $current = StudentRapor::where('academic_year_id', $activeYear->id)
                            ->whereIn('student_id', $studentIds)
                            ->where('is_published', true)
                            ->count();

                        $percent = $expected > 0 ? round(($current / $expected) * 100, 1) : 0;
                        if ($percent > 100) {
                            $percent = 100;
                        }

                        return "{$current} / {$expected} ({$percent}%)";
                    })
                    ->badge()
                    ->color(fn (string $state): string => str_contains($state, '(100%)') ? 'success' : (str_contains($state, '(0%)') ? 'danger' : 'primary')),
            ])
            ->actions([
                Action::make('cek_tanggungan')
                    ->label('Cek Tanggungan')
                    ->icon('heroicon-m-clipboard-document-check')
                    ->modalHeading(fn (StudyGroup $record) => 'Tanggungan: '.$record->nama_rombel)
                    ->modalContent(function (StudyGroup $record) use ($activeYear) {
                        $tanggungan = $this->getTanggungan($record, $activeYear);

                        return view('filament.widgets.tanggungan-rombel', [
                            'tanggungan' => $tanggungan,
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
            ])
            ->recordAction('cek_tanggungan')
            ->defaultSort('nama_rombel', 'asc')
            ->paginated([10, 25, 50, 'all']);
    }

    private function getTanggungan(StudyGroup $record, $activeYear): array
    {
        $tanggungan = [];
        if (! $activeYear) {
            return $tanggungan;
        }

        $studentIds = $record->students()->pluck('students.id');

        if ($nilai = $this->getTanggunganNilai($record, $activeYear, $studentIds)) {
            $tanggungan[] = $nilai;
        }
        if ($rapor = $this->getTanggunganCetakRapor($record, $activeYear, $studentIds)) {
            $tanggungan[] = $rapor;
        }
        if ($presensi = $this->getTanggunganPresensi($record, $activeYear, $studentIds)) {
            $tanggungan[] = $presensi;
        }
        if ($ekskul = $this->getTanggunganEkskul($record, $activeYear, $studentIds)) {
            $tanggungan[] = $ekskul;
        }
        if ($catatan = $this->getTanggunganCatatanWali($record, $activeYear, $studentIds)) {
            $tanggungan[] = $catatan;
        }
        if ($publikasi = $this->getTanggunganPublikasiRapor($record, $activeYear, $studentIds)) {
            $tanggungan[] = $publikasi;
        }

        return $tanggungan;
    }

    private function getTanggunganNilai($record, $activeYear, $studentIds): ?array
    {
        $gradedSubjectsCount = Schedule::where('study_group_id', $record->id)
            ->whereHas('subject', fn ($sq) => $sq->where('is_graded', true))
            ->distinct('subject_id')
            ->count('subject_id');
        $expectedGrades = $studentIds->count() * $gradedSubjectsCount;
        $currentGrades = Grade::where('academic_year_id', $activeYear->id)
            ->where('study_group_id', $record->id)
            ->count();
        if ($expectedGrades > 0 && $currentGrades < $expectedGrades) {
            $studentGradesCount = Grade::where('academic_year_id', $activeYear->id)
                ->where('study_group_id', $record->id)
                ->selectRaw('student_id, count(*) as count')
                ->groupBy('student_id')
                ->pluck('count', 'student_id')
                ->toArray();

            $missingIds = [];
            foreach ($studentIds as $sId) {
                if (($studentGradesCount[$sId] ?? 0) < $gradedSubjectsCount) {
                    $missingIds[] = $sId;
                }
            }

            $missingNames = '';
            if (count($missingIds) > 0) {
                $missingNames = Student::whereIn('id', $missingIds)->with('user')->get()->pluck('user.name')->take(5)->implode(', ');
                if (count($missingIds) > 5) {
                    $missingNames .= ' (+'.(count($missingIds) - 5).' siswa lainnya)';
                }
            }

            $percent = round(($currentGrades / $expectedGrades) * 100, 1);

            return [
                'title' => 'Input Nilai',
                'text' => "{$currentGrades} / {$expectedGrades} ({$percent}%)",
                'missing' => 'Belum dinilai: '.$missingNames,
                'url' => GradeResource::getUrl('index', ['action' => 'batch_input', 'study_group_id' => $record->id, 'missing_only' => 1]),
            ];
        }

        return null;
    }

    private function getTanggunganCetakRapor($record, $activeYear, $studentIds): ?array
    {
        $expectedRapor = $studentIds->count();
        $currentRapor = StudentRapor::where('academic_year_id', $activeYear->id)
            ->whereIn('student_id', $studentIds)
            ->pluck('student_id')->toArray();

        if ($expectedRapor > 0 && count($currentRapor) < $expectedRapor) {
            $missingIds = array_diff($studentIds->toArray(), $currentRapor);
            $missingNames = Student::whereIn('id', $missingIds)->with('user')->get()->pluck('user.name')->take(5)->implode(', ');
            if (count($missingIds) > 5) {
                $missingNames .= ' (+'.(count($missingIds) - 5).' lainnya)';
            }

            $percent = round((count($currentRapor) / $expectedRapor) * 100, 1);

            return [
                'title' => 'Cetak Rapor',
                'text' => count($currentRapor)." / {$expectedRapor} ({$percent}%)",
                'missing' => 'Belum dicetak: '.$missingNames,
                'url' => RaporResource::getUrl('index', ['tableFilters' => ['rombel_filter' => ['study_group_id' => $record->id, 'academic_year_id' => $activeYear->id], 'status_rapor' => ['value' => 'belum_cetak']]]),
            ];
        }

        return null;
    }

    private function getTanggunganPresensi($record, $activeYear, $studentIds): ?array
    {
        $expectedPresensi = $studentIds->count();
        $currentPresensi = Attendance::whereIn('student_id', $studentIds)
            ->where('tanggal', now()->toDateString())
            ->pluck('student_id')->toArray();

        if ($expectedPresensi > 0 && count($currentPresensi) < $expectedPresensi) {
            $missingIds = array_diff($studentIds->toArray(), $currentPresensi);
            $missingNames = Student::whereIn('id', $missingIds)->with('user')->get()->pluck('user.name')->take(5)->implode(', ');
            if (count($missingIds) > 5) {
                $missingNames .= ' (+'.(count($missingIds) - 5).' lainnya)';
            }

            $percent = round((count($currentPresensi) / $expectedPresensi) * 100, 1);

            return [
                'title' => 'Presensi Hari Ini',
                'text' => count($currentPresensi)." / {$expectedPresensi} ({$percent}%)",
                'missing' => 'Belum diabsen: '.$missingNames,
                'url' => AttendanceResource::getUrl('index', ['action' => 'batch_input', 'study_group_id' => $record->id, 'tanggal' => now()->toDateString(), 'missing_only' => 1]),
            ];
        }

        return null;
    }

    private function getTanggunganEkskul($record, $activeYear, $studentIds): ?array
    {
        $expectedEkskul = DB::table('extracurricular_student')
            ->whereIn('student_id', $studentIds)
            ->get();

        $expectedCount = $expectedEkskul->count();

        $currentEkskul = ExtracurricularGrade::where('academic_year_id', $activeYear->id)
            ->whereIn('student_id', $studentIds)
            ->whereExists(function ($q) {
                $q->selectRaw('1')
                    ->from('extracurricular_student')
                    ->whereColumn('extracurricular_student.student_id', 'extracurricular_grades.student_id')
                    ->whereColumn('extracurricular_student.extracurricular_id', 'extracurricular_grades.extracurricular_id');
            })
            ->get();

        $currentCount = $currentEkskul->count();

        if ($expectedCount > 0 && $currentCount < $expectedCount) {
            // Find missing
            $missingDetails = [];
            foreach ($expectedEkskul as $exp) {
                $hasGrade = $currentEkskul->where('student_id', $exp->student_id)->where('extracurricular_id', $exp->extracurricular_id)->first();
                if (! $hasGrade) {
                    $missingDetails[] = [
                        'student_id' => $exp->student_id,
                        'extracurricular_id' => $exp->extracurricular_id,
                    ];
                }
            }

            $missingNames = '';
            if (count($missingDetails) > 0) {
                $missingStudentIds = collect($missingDetails)->pluck('student_id')->unique();
                $ekskulIds = collect($missingDetails)->pluck('extracurricular_id')->unique();

                $studentsMap = Student::whereIn('id', $missingStudentIds)->with('user')->get()->keyBy('id');
                $ekskulsMap = Extracurricular::whereIn('id', $ekskulIds)->pluck('nama_ekskul', 'id');

                $formattedMissing = [];
                $count = 0;
                foreach ($missingDetails as $detail) {
                    if ($count >= 5) {
                        break;
                    }
                    $studentName = $studentsMap[$detail['student_id']]->user->name ?? 'Unknown';
                    $ekskulName = $ekskulsMap[$detail['extracurricular_id']] ?? 'Unknown';
                    $formattedMissing[] = "{$studentName} ({$ekskulName})";
                    $count++;
                }

                $missingNames = implode(', ', $formattedMissing);
                if (count($missingDetails) > 5) {
                    $missingNames .= ' (+'.(count($missingDetails) - 5).' lainnya)';
                }
            }

            $percent = round(($currentCount / $expectedCount) * 100, 1);

            return [
                'title' => 'Nilai Ekstrakurikuler',
                'text' => "{$currentCount} / {$expectedCount} ({$percent}%)",
                'missing' => 'Belum dinilai: '.$missingNames,
                'url' => ExtracurricularGradeResource::getUrl('batch-input', ['rombel_id' => $record->id, 'missing_only' => 1]),
            ];
        }

        return null;
    }

    private function getTanggunganCatatanWali($record, $activeYear, $studentIds): ?array
    {
        $expectedCatatan = $studentIds->count();
        $currentCatatan = StudentRapor::where('academic_year_id', $activeYear->id)
            ->whereIn('student_id', $studentIds)
            ->whereNotNull('catatan_wali_kelas')
            ->where('catatan_wali_kelas', '!=', '')
            ->pluck('student_id')->toArray();

        if ($expectedCatatan > 0 && count($currentCatatan) < $expectedCatatan) {
            $missingIds = array_diff($studentIds->toArray(), $currentCatatan);
            $missingNames = Student::whereIn('id', $missingIds)->with('user')->get()->pluck('user.name')->take(5)->implode(', ');
            if (count($missingIds) > 5) {
                $missingNames .= ' (+'.(count($missingIds) - 5).' lainnya)';
            }

            $percent = round((count($currentCatatan) / $expectedCatatan) * 100, 1);

            return [
                'title' => 'Catatan Wali Kelas',
                'text' => count($currentCatatan)." / {$expectedCatatan} ({$percent}%)",
                'missing' => 'Belum ada catatan: '.$missingNames,
                'url' => RaporResource::getUrl('index', ['tableFilters' => ['rombel_filter' => ['study_group_id' => $record->id, 'academic_year_id' => $activeYear->id], 'status_rapor' => ['value' => 'belum_catatan']]]),
            ];
        }

        return null;
    }

    private function getTanggunganPublikasiRapor($record, $activeYear, $studentIds): ?array
    {
        $expectedPublikasi = $studentIds->count();
        $currentPublikasi = StudentRapor::where('academic_year_id', $activeYear->id)
            ->whereIn('student_id', $studentIds)
            ->where('is_published', true)
            ->pluck('student_id')->toArray();

        if ($expectedPublikasi > 0 && count($currentPublikasi) < $expectedPublikasi) {
            $missingIds = array_diff($studentIds->toArray(), $currentPublikasi);
            $missingNames = Student::whereIn('id', $missingIds)->with('user')->get()->pluck('user.name')->take(5)->implode(', ');
            if (count($missingIds) > 5) {
                $missingNames .= ' (+'.(count($missingIds) - 5).' lainnya)';
            }

            $percent = round((count($currentPublikasi) / $expectedPublikasi) * 100, 1);

            return [
                'title' => 'Publikasi Rapor',
                'text' => count($currentPublikasi)." / {$expectedPublikasi} ({$percent}%)",
                'missing' => 'Belum dipublikasi: '.$missingNames,
                'url' => RaporResource::getUrl('index', ['tableFilters' => ['rombel_filter' => ['study_group_id' => $record->id, 'academic_year_id' => $activeYear->id], 'status_rapor' => ['value' => 'belum_publikasi']]]),
            ];
        }

        return null;
    }
}
