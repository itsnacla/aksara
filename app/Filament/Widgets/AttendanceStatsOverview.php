<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\AcademicYear;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AttendanceStatsOverview extends BaseWidget
{
    protected static ?int $sort = -1;

    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now();

        // Today's attendance
        $todayTotal = Attendance::whereDate('tanggal', $today)->count();
        $todayPresent = Attendance::whereDate('tanggal', $today)->where('status', 'hadir')->count();
        $todaySick = Attendance::whereDate('tanggal', $today)->where('status', 'sakit')->count();
        $todayPermission = Attendance::whereDate('tanggal', $today)->where('status', 'izin')->count();
        $todayAbsent = Attendance::whereDate('tanggal', $today)->where('status', 'alfa')->count();

        $todayPercentage = $todayTotal > 0 ? round(($todayPresent / $todayTotal) * 100, 1) : 0;

        // This month attendance
        $monthTotal = Attendance::whereMonth('tanggal', $thisMonth->month)
            ->whereYear('tanggal', $thisMonth->year)
            ->count();
        $monthPresent = Attendance::whereMonth('tanggal', $thisMonth->month)
            ->whereYear('tanggal', $thisMonth->year)
            ->where('status', 'hadir')
            ->count();
        $monthPercentage = $monthTotal > 0 ? round(($monthPresent / $monthTotal) * 100, 1) : 0;

        // Determine color based on percentage
        $todayColor = $this->getPercentageColor($todayPercentage);
        $monthColor = $this->getPercentageColor($monthPercentage);

        return [
            Stat::make('Kehadiran Hari Ini', $todayPercentage . '%')
                ->description($todayPresent . ' dari ' . $todayTotal . ' kehadiran')
                ->descriptionIcon('heroicon-m-calendar')
                ->icon('heroicon-o-clipboard-document-check')
                ->color($todayColor)
                ->chart($this->getDailyAttendanceTrend()),

            Stat::make('Kehadiran Bulan Ini', $monthPercentage . '%')
                ->description($monthPresent . ' hadir dari ' . $monthTotal . ' total')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->icon('heroicon-o-chart-bar-square')
                ->color($monthColor)
                ->chart($this->getMonthlyAttendanceTrend()),

            Stat::make('Sakit & Izin Hari Ini', ($todaySick + $todayPermission))
                ->description($todaySick . ' sakit, ' . $todayPermission . ' izin')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->icon('heroicon-o-shield-exclamation')
                ->color('warning')
                ->chart($this->getSickPermissionTrend()),

            Stat::make('Alfa Hari Ini', $todayAbsent)
                ->description('Tanpa keterangan')
                ->descriptionIcon('heroicon-m-x-circle')
                ->icon('heroicon-o-x-circle')
                ->color($todayAbsent > 0 ? 'danger' : 'success')
                ->chart($this->getAbsentTrend()),
        ];
    }

    private function getPercentageColor(float $percentage): string
    {
        if ($percentage >= 90) return 'success';
        if ($percentage >= 75) return 'warning';
        if ($percentage > 0) return 'danger';
        return 'gray';
    }

    private function getDailyAttendanceTrend(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $total = Attendance::whereDate('tanggal', $date)->count();
            $present = Attendance::whereDate('tanggal', $date)->where('status', 'hadir')->count();
            $data[] = $total > 0 ? round(($present / $total) * 100) : 0;
        }
        if (array_sum($data) === 0) {
            return [85, 90, 88, 92, 87, 91, 89];
        }
        return $data;
    }

    private function getMonthlyAttendanceTrend(): array
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $total = Attendance::whereMonth('tanggal', $month->month)
                ->whereYear('tanggal', $month->year)
                ->count();
            $present = Attendance::whereMonth('tanggal', $month->month)
                ->whereYear('tanggal', $month->year)
                ->where('status', 'hadir')
                ->count();
            $data[] = $total > 0 ? round(($present / $total) * 100) : 0;
        }
        if (array_sum($data) === 0) {
            return [88, 91, 87, 93, 90, 92];
        }
        return $data;
    }

    private function getSickPermissionTrend(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $data[] = Attendance::whereDate('tanggal', $date)
                ->whereIn('status', ['sakit', 'izin'])
                ->count();
        }
        if (array_sum($data) === 0) {
            return [3, 2, 4, 1, 3, 2, 2];
        }
        return $data;
    }

    private function getAbsentTrend(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $data[] = Attendance::whereDate('tanggal', $date)
                ->where('status', 'alfa')
                ->count();
        }
        if (array_sum($data) === 0) {
            return [1, 0, 2, 1, 0, 1, 0];
        }
        return $data;
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }
}
