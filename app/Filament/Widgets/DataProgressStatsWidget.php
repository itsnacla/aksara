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

        $studyGroupsQuery = StudyGroup::where('academic_year_id', $activeYear->id);
        
        if (auth()->user() && auth()->user()->hasRole('guru')) {
            $teacherId = auth()->user()->teacher?->id;
            if ($teacherId) {
                $studyGroupsQuery->where(function($q) use ($teacherId) {
                    $q->where('walikelas_id', $teacherId)
                      ->orWhereHas('schedules', fn($sq) => $sq->where('teacher_id', $teacherId));
                });
            } else {
                $studyGroupsQuery->where('id', 0);
            }
        }

        $studyGroups = $studyGroupsQuery->withCount(['students', 'schedules'])->get();
        // 1. Nilai Progress
        $expectedGrades = 0;
        foreach ($studyGroups as $sg) {
            $expectedGrades += ($sg->students_count * $sg->schedules_count);
        }
        $currentGrades = Grade::where('academic_year_id', $activeYear->id)
            ->whereIn('study_group_id', $studyGroups->pluck('id'))
            ->count();
        $gradePercent = $expectedGrades > 0 ? round(($currentGrades / $expectedGrades) * 100, 1) : 0;
        if ($gradePercent > 100) $gradePercent = 100;

        // 2. Rapor Progress
        $expectedRapor = $studyGroups->sum('students_count');
        $currentRapor = StudentRapor::where('academic_year_id', $activeYear->id)
            ->whereIn('student_id', $studyGroups->flatMap->students->pluck('id'))
            ->count();
        $raporPercent = $expectedRapor > 0 ? round(($currentRapor / $expectedRapor) * 100, 1) : 0;
        if ($raporPercent > 100) $raporPercent = 100;

        // 3. Data Siswa
        $totalStudents = Student::whereHas('studyGroups', fn($q) => $q->whereIn('study_groups.id', $studyGroups->pluck('id')))->count();
        $studentMissingNisn = Student::whereHas('studyGroups', fn($q) => $q->whereIn('study_groups.id', $studyGroups->pluck('id')))
            ->where(function($q) {
                $q->whereNull('nisn')->orWhere('nisn', '');
            })->count();
        $studentPercent = $totalStudents > 0 ? round((($totalStudents - $studentMissingNisn) / $totalStudents) * 100, 1) : 0;

        // 4. Data Pegawai
        $totalStaff = Staff::count() + Teacher::count();
        $staffMissingData = Staff::whereNull('no_whatsapp')->orWhere('no_whatsapp', '')->count() 
            + Teacher::whereNull('nip')->orWhere('nip', '')->count();
        $staffPercent = $totalStaff > 0 ? round((($totalStaff - $staffMissingData) / $totalStaff) * 100, 1) : 0;

        // 5. Rombel
        $rombelNoSchedule = $studyGroups->where('schedules_count', 0)->count();

        return [
            Stat::make('Progress Input Nilai', number_format($currentGrades) . ' / ' . number_format($expectedGrades))
                ->description($gradePercent . '% selesai. Di TA: ' . $activeYear->tahun_ajaran)
                ->descriptionIcon($gradePercent >= 100 ? 'heroicon-m-check-circle' : 'heroicon-m-arrow-trending-up')
                ->color($gradePercent >= 100 ? 'success' : 'warning')
                ->chart([0, $gradePercent, $gradePercent]),

            Stat::make('Progress Cetak Rapor', number_format($currentRapor) . ' / ' . number_format($expectedRapor))
                ->description($raporPercent . '% selesai mencetak rapor.')
                ->descriptionIcon($raporPercent >= 100 ? 'heroicon-m-check-circle' : 'heroicon-m-document-text')
                ->color($raporPercent >= 100 ? 'success' : 'primary')
                ->chart([0, $raporPercent, $raporPercent]),

            Stat::make('Kelengkapan Data Siswa', number_format($totalStudents - $studentMissingNisn) . ' / ' . number_format($totalStudents))
                ->description($studentMissingNisn > 0 ? $studentMissingNisn . ' siswa belum memiliki NISN' : 'Semua siswa memiliki NISN')
                ->descriptionIcon($studentMissingNisn > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($studentMissingNisn > 0 ? 'danger' : 'success'),

            Stat::make('Kelengkapan Data Pegawai', number_format($totalStaff - $staffMissingData) . ' / ' . number_format($totalStaff))
                ->description($staffMissingData > 0 ? $staffMissingData . ' pegawai datanya belum lengkap' : 'Semua data pegawai lengkap')
                ->descriptionIcon($staffMissingData > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($staffMissingData > 0 ? 'warning' : 'success'),

            Stat::make('Rombel Tanpa Jadwal', number_format($rombelNoSchedule))
                ->description('Dari total ' . $studyGroups->count() . ' rombel aktif')
                ->descriptionIcon($rombelNoSchedule > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($rombelNoSchedule > 0 ? 'danger' : 'success'),
        ];
    }
}
