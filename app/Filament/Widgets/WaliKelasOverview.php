<?php

namespace App\Filament\Widgets;

use App\Models\StudyGroup;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use App\Models\AcademicYear;

class WaliKelasOverview extends BaseWidget
{
    protected static ?int $sort = -2;

    protected function getStats(): array
    {
        $user = Auth::user();

        // Only show for Teachers
        if (!$user->hasRole('guru')) {
            return [];
        }

        $studyGroup = StudyGroup::where('walikelas_id', $user->teacher->id ?? 0)
            ->withCount('students')
            ->first();

        if (!$studyGroup) {
            return [
                Stat::make('Status Wali Kelas', 'Belum Diatur')
                    ->description('Anda belum ditugaskan sebagai wali kelas di rombel manapun.')
                    ->color('gray'),
            ];
        }

        return [
            Stat::make('Rombel Anda', $studyGroup->nama_rombel)
                ->description($studyGroup->academicYear->tahun_ajaran ?? 'Tahun Ajaran Aktif')
                ->icon('heroicon-m-user-group')
                ->color('primary')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'onclick' => "window.location.href='/admin/rombel/{$studyGroup->id}/edit'",
                ]),
            Stat::make('Total Siswa', $studyGroup->students_count)
                ->description('Klik untuk lihat daftar siswa')
                ->icon('heroicon-m-users')
                ->color('success')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'onclick' => "window.location.href='/admin/rombel/{$studyGroup->id}/edit'",
                ]),
            Stat::make('Presensi Hari Ini', 'Klik Disini')
                ->description('Mulai input presensi harian')
                ->icon('heroicon-m-clipboard-document-check')
                ->color('warning')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'onclick' => "window.location.href='/admin/rombel/{$studyGroup->id}/edit'",
                ]),
            Stat::make('Rata-rata Kelas', $this->getAverageGrade($studyGroup))
                ->description('Performa akademik kelas')
                ->icon('heroicon-m-chart-bar')
                ->color('info'),
        ];
    }

    private function getAverageGrade($studyGroup): string
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $students = $studyGroup->students;

        if ($students->isEmpty()) {
            return '-';
        }

        $totalAverage = 0;
        $count = 0;

        foreach ($students as $student) {
            $grades = $student->grades()
                ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
                ->get();

            if (!$grades->isEmpty()) {
                $avg = $grades->avg(function ($g) {
                    return ($g->nilai_tugas + $g->nilai_uts + $g->nilai_uas) / 3;
                });
                $totalAverage += $avg;
                $count++;
            }
        }

        return $count > 0 ? round($totalAverage / $count, 1) : '-';
    }

    public static function canView(): bool
    {
        return auth()->user()->hasRole('guru');
    }
}
