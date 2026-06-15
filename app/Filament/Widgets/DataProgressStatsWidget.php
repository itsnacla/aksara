<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\AcademicYear;
use App\Models\StudyGroup;
use App\Models\Student;
use App\Models\Grade;
use App\Models\StudentRapor;
use App\Models\Staff;
use App\Models\Teacher;

class DataProgressStatsWidget extends BaseWidget
{
    protected ?string $pollingInterval = null;

    /**
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        $activeYear = AcademicYear::where('is_active', true)->first();

        if (!$activeYear) {
            return [
                Stat::make('Tahun Ajaran Aktif', 'Tidak ada data')
                    ->description('Silakan aktifkan tahun ajaran terlebih dahulu.')
                    ->color('danger'),
            ];
        }

        $studyGroups = $this->getStudyGroups($activeYear);

        return [
            $this->getGradeProgressStat($activeYear, $studyGroups),
            $this->getExtracurricularProgressStat($activeYear, $studyGroups),
            $this->getRaporProgressStat($activeYear, $studyGroups),
            $this->getCatatanWaliProgressStat($activeYear, $studyGroups),
            $this->getPublikasiRaporProgressStat($activeYear, $studyGroups),
            $this->getAttendanceProgressStat($studyGroups),
            $this->getStudentDataStat($studyGroups),
            $this->getStaffDataStat(),
            $this->getScheduleStat($studyGroups),
        ];
    }

    private function getStudyGroups($activeYear)
    {
        $query = StudyGroup::where('academic_year_id', $activeYear->id);
        
        if (auth()->user() && auth()->user()->hasRole('guru')) {
            $teacherId = auth()->user()->teacher?->id;
            if ($teacherId) {
                $query->where(function($q) use ($teacherId) {
                    $q->where('walikelas_id', $teacherId)
                      ->orWhereHas('schedules', fn($sq) => $sq->where('teacher_id', $teacherId));
                });
            } else {
                $query->where('id', 0);
            }
        }

        return $query->withCount([
            'students',
            'schedules' => function ($q) {
                $q->whereHas('subject', function ($sq) {
                    $sq->where('is_graded', true);
                });
            }
        ])->get();
    }

    private function getGradeProgressStat($activeYear, $studyGroups): Stat
    {
        $expectedGrades = 0;
        foreach ($studyGroups as $sg) {
            $expectedGrades += ($sg->students_count * $sg->schedules_count);
        }
        $currentGrades = Grade::where('academic_year_id', $activeYear->id)
            ->whereIn('study_group_id', $studyGroups->pluck('id'))
            ->count();
        $gradePercent = $expectedGrades > 0 ? round(($currentGrades / $expectedGrades) * 100, 1) : 0;
        if ($gradePercent > 100) $gradePercent = 100;

        return Stat::make('Progress Input Nilai', number_format($currentGrades) . ' / ' . number_format($expectedGrades))
            ->description($gradePercent . '% selesai. Di TA: ' . $activeYear->tahun_ajaran)
            ->descriptionIcon($gradePercent >= 100 ? 'heroicon-m-check-circle' : 'heroicon-m-arrow-trending-up')
            ->color($gradePercent >= 100 ? 'success' : 'warning')
            ->chart([0, $gradePercent, $gradePercent]);
    }

    private function getRaporProgressStat($activeYear, $studyGroups): Stat
    {
        $expectedRapor = $studyGroups->sum('students_count');
        $currentRapor = StudentRapor::where('academic_year_id', $activeYear->id)
            ->whereHas('student.studyGroups', function($q) use ($studyGroups) {
                $q->whereIn('study_groups.id', $studyGroups->pluck('id'));
            })
            ->count();
        $raporPercent = $expectedRapor > 0 ? round(($currentRapor / $expectedRapor) * 100, 1) : 0;
        if ($raporPercent > 100) $raporPercent = 100;

        return Stat::make('Progress Cetak Rapor', number_format($currentRapor) . ' / ' . number_format($expectedRapor))
            ->description($raporPercent . '% selesai mencetak rapor.')
            ->descriptionIcon($raporPercent >= 100 ? 'heroicon-m-check-circle' : 'heroicon-m-document-text')
            ->color($raporPercent >= 100 ? 'success' : 'primary')
            ->chart([0, $raporPercent, $raporPercent]);
    }

    private function getStudentDataStat($studyGroups): Stat
    {
        $totalStudents = Student::whereHas('studyGroups', fn($q) => $q->whereIn('study_groups.id', $studyGroups->pluck('id')))->count();
        $studentMissingNisn = Student::whereHas('studyGroups', fn($q) => $q->whereIn('study_groups.id', $studyGroups->pluck('id')))
            ->where(function($q) {
                $q->whereNull('nisn')->orWhere('nisn', '');
            })->count();

        return Stat::make('Kelengkapan Data Siswa', number_format($totalStudents - $studentMissingNisn) . ' / ' . number_format($totalStudents))
            ->description($studentMissingNisn > 0 ? $studentMissingNisn . ' siswa belum memiliki NISN' : 'Semua siswa memiliki NISN')
            ->descriptionIcon($studentMissingNisn > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
            ->color($studentMissingNisn > 0 ? 'danger' : 'success');
    }

    private function getStaffDataStat(): Stat
    {
        $totalStaff = Staff::count() + Teacher::count();
        $staffMissingData = Staff::whereNull('no_whatsapp')->orWhere('no_whatsapp', '')->count() 
            + Teacher::whereNull('nip')->orWhere('nip', '')->count();

        return Stat::make('Kelengkapan Data Pegawai', number_format($totalStaff - $staffMissingData) . ' / ' . number_format($totalStaff))
            ->description($staffMissingData > 0 ? $staffMissingData . ' pegawai datanya belum lengkap' : 'Semua data pegawai lengkap')
            ->descriptionIcon($staffMissingData > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
            ->color($staffMissingData > 0 ? 'warning' : 'success');
    }

    private function getScheduleStat($studyGroups): Stat
    {
        $rombelNoSchedule = $studyGroups->where('schedules_count', 0)->count();

        return Stat::make('Rombel Tanpa Jadwal', number_format($rombelNoSchedule))
            ->description('Dari total ' . $studyGroups->count() . ' rombel aktif')
            ->descriptionIcon($rombelNoSchedule > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
            ->color($rombelNoSchedule > 0 ? 'danger' : 'success');
    }

    private function getExtracurricularProgressStat($activeYear, $studyGroups): Stat
    {
        $studentIds = Student::whereHas('studyGroups', function($q) use ($studyGroups) {
            $q->whereIn('study_groups.id', $studyGroups->pluck('id'));
        })->pluck('id');

        $expected = \Illuminate\Support\Facades\DB::table('extracurricular_student')
            ->whereIn('student_id', $studentIds)
            ->count();
            
        $current = \App\Models\ExtracurricularGrade::where('academic_year_id', $activeYear->id)
            ->whereIn('student_id', $studentIds)
            ->whereExists(function($q) {
                $q->selectRaw('1')
                  ->from('extracurricular_student')
                  ->whereColumn('extracurricular_student.student_id', 'extracurricular_grades.student_id')
                  ->whereColumn('extracurricular_student.extracurricular_id', 'extracurricular_grades.extracurricular_id');
            })
            ->count();
        
        $percent = $expected > 0 ? round(($current / $expected) * 100, 1) : 0;
        if ($percent > 100) $percent = 100;

        return Stat::make('Progress Nilai Ekstrakurikuler', number_format($current) . ' / ' . number_format($expected))
            ->description($percent . '% selesai dinilai.')
            ->descriptionIcon($percent >= 100 ? 'heroicon-m-check-circle' : 'heroicon-m-arrow-trending-up')
            ->color($percent >= 100 ? 'success' : 'warning')
            ->chart([0, $percent, $percent]);
    }

    private function getAttendanceProgressStat($studyGroups): Stat
    {
        $studentIds = Student::whereHas('studyGroups', function($q) use ($studyGroups) {
            $q->whereIn('study_groups.id', $studyGroups->pluck('id'));
        })->pluck('id');
        
        $expected = $studentIds->count();
        $current = \App\Models\Attendance::whereIn('student_id', $studentIds)
            ->where('tanggal', now()->toDateString())
            ->count();
            
        $percent = $expected > 0 ? round(($current / $expected) * 100, 1) : 0;
        if ($percent > 100) $percent = 100;

        return Stat::make('Progress Presensi Hari Ini', number_format($current) . ' / ' . number_format($expected))
            ->description($percent . '% siswa sudah diabsen.')
            ->descriptionIcon($percent >= 100 ? 'heroicon-m-check-circle' : 'heroicon-m-clock')
            ->color($percent >= 100 ? 'success' : 'warning')
            ->chart([0, $percent, $percent]);
    }

    private function getCatatanWaliProgressStat($activeYear, $studyGroups): Stat
    {
        $expected = $studyGroups->sum('students_count');
        $current = StudentRapor::where('academic_year_id', $activeYear->id)
            ->whereHas('student.studyGroups', function($q) use ($studyGroups) {
                $q->whereIn('study_groups.id', $studyGroups->pluck('id'));
            })
            ->whereNotNull('catatan_wali_kelas')
            ->where('catatan_wali_kelas', '!=', '')
            ->count();
            
        $percent = $expected > 0 ? round(($current / $expected) * 100, 1) : 0;
        if ($percent > 100) $percent = 100;

        return Stat::make('Progress Catatan Wali Kelas', number_format($current) . ' / ' . number_format($expected))
            ->description($percent . '% catatan wali kelas diisi.')
            ->descriptionIcon($percent >= 100 ? 'heroicon-m-check-circle' : 'heroicon-m-pencil-square')
            ->color($percent >= 100 ? 'success' : 'warning')
            ->chart([0, $percent, $percent]);
    }

    private function getPublikasiRaporProgressStat($activeYear, $studyGroups): Stat
    {
        $expected = $studyGroups->sum('students_count');
        $current = StudentRapor::where('academic_year_id', $activeYear->id)
            ->whereHas('student.studyGroups', function($q) use ($studyGroups) {
                $q->whereIn('study_groups.id', $studyGroups->pluck('id'));
            })
            ->where('is_published', true)
            ->count();
            
        $percent = $expected > 0 ? round(($current / $expected) * 100, 1) : 0;
        if ($percent > 100) $percent = 100;

        return Stat::make('Progress Publikasi Rapor', number_format($current) . ' / ' . number_format($expected))
            ->description($percent . '% rapor siap dilihat siswa.')
            ->descriptionIcon($percent >= 100 ? 'heroicon-m-check-circle' : 'heroicon-m-globe-alt')
            ->color($percent >= 100 ? 'success' : 'warning')
            ->chart([0, $percent, $percent]);
    }
}
