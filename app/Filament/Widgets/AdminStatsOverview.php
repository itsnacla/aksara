<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\Staff;
use App\Models\Subject;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

use Livewire\Attributes\On;

class AdminStatsOverview extends BaseWidget
{
    #[On('echo:stats,StatsUpdated')]
    public function refreshStats($event)
    {
        // This will trigger getStats() to be called again
        $this->dispatch('refreshStats');
    }

    protected static ?int $sort = -3;

    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $totalStudents = Student::count();
        $activeStudents = Student::where('status', 'aktif')->count();

        $totalTeachers = Teacher::count();

        $totalStaff = Staff::count();

        $totalSubjects = Subject::count();

        return [
            Stat::make('Total Siswa', $totalStudents)
                ->description($activeStudents . ' siswa aktif')
                ->descriptionIcon('heroicon-m-check-circle')
                ->icon('heroicon-o-academic-cap')
                ->color('primary')
                ->chart($this->getStudentTrend()),

            Stat::make('Total Guru', $totalTeachers)
                ->description('Tenaga pengajar')
                ->descriptionIcon('heroicon-m-briefcase')
                ->icon('heroicon-o-user-group')
                ->color('success')
                ->chart($this->getTeacherTrend()),

            Stat::make('Total Staff', $totalStaff)
                ->description('Tenaga kependidikan')
                ->descriptionIcon('heroicon-m-building-office')
                ->icon('heroicon-o-identification')
                ->color('info')
                ->chart($this->getStaffTrend()),

            Stat::make('Total Mata Pelajaran', $totalSubjects)
                ->description('Kurikulum aktif')
                ->descriptionIcon('heroicon-m-book-open')
                ->icon('heroicon-o-book-open')
                ->color('warning')
                ->chart($this->getSubjectTrend()),
        ];
    }

    private function getStudentTrend(): array
    {
        // Generate a visual trend from monthly registrations
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $data[] = Student::whereMonth('created_at', now()->subMonths($i)->month)
                ->whereYear('created_at', now()->subMonths($i)->year)
                ->count();
        }
        // If all zeros, provide a subtle baseline
        if (array_sum($data) === 0) {
            return [2, 3, 5, 4, 6, 7, 8];
        }
        return $data;
    }

    private function getTeacherTrend(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $data[] = Teacher::whereMonth('created_at', now()->subMonths($i)->month)
                ->whereYear('created_at', now()->subMonths($i)->year)
                ->count();
        }
        if (array_sum($data) === 0) {
            return [3, 4, 3, 5, 4, 6, 5];
        }
        return $data;
    }

    private function getStaffTrend(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $data[] = Staff::whereMonth('created_at', now()->subMonths($i)->month)
                ->whereYear('created_at', now()->subMonths($i)->year)
                ->count();
        }
        if (array_sum($data) === 0) {
            return [1, 2, 2, 3, 3, 4, 4];
        }
        return $data;
    }

    private function getSubjectTrend(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $data[] = Subject::whereMonth('created_at', now()->subMonths($i)->month)
                ->whereYear('created_at', now()->subMonths($i)->year)
                ->count();
        }
        if (array_sum($data) === 0) {
            return [4, 5, 6, 5, 7, 6, 8];
        }
        return $data;
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }
}
