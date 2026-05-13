<?php

namespace App\Filament\Widgets;

use App\Models\AcademicYear;
use App\Models\StudyGroup;
use App\Models\Schedule;
use App\Models\StudentLeave;
use App\Models\SchoolSetting;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AcademicInfoOverview extends BaseWidget
{
    protected static ?int $sort = -2;

    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $totalRombel = StudyGroup::query()
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->count();

        $totalSchedules = Schedule::count();

        $pendingLeaves = StudentLeave::where('status', 'pending')->count();

        return [
            Stat::make('Tahun Ajaran Aktif', $activeYear ? $activeYear->tahun_ajaran : 'Belum Diatur')
                ->description($activeYear ? 'Semester ' . $activeYear->semester : 'Silakan atur tahun ajaran')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->icon('heroicon-o-calendar-days')
                ->color('primary'),

            Stat::make('Total Rombel', $totalRombel)
                ->description('Rombongan belajar aktif')
                ->descriptionIcon('heroicon-m-rectangle-group')
                ->icon('heroicon-o-rectangle-group')
                ->color('success'),

            Stat::make('Total Jadwal', $totalSchedules)
                ->description('Sesi pelajaran terjadwal')
                ->descriptionIcon('heroicon-m-clock')
                ->icon('heroicon-o-clock')
                ->color('info'),

            Stat::make('Pengajuan Izin', $pendingLeaves)
                ->description('Menunggu persetujuan')
                ->descriptionIcon('heroicon-m-document-check')
                ->icon('heroicon-o-document-check')
                ->color($pendingLeaves > 0 ? 'warning' : 'success'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }
}
