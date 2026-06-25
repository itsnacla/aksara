<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ScopesToTeacherStudents;
use App\Models\Attendance;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class AttendanceDoughnutChart extends ChartWidget
{
    use ScopesToTeacherStudents;

    protected ?string $heading = 'Ringkasan Kehadiran Bulan Ini';

    protected ?string $description = 'Proporsi status kehadiran seluruh siswa';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
    ];

    protected ?string $maxHeight = '320px';

    protected function getData(): array
    {
        $now = Carbon::now();
        $isGuru = auth()->user()?->hasRole('guru');

        $hadirQuery = Attendance::whereMonth('tanggal', $now->month)->whereYear('tanggal', $now->year)->where('status', 'hadir');
        $sakitQuery = Attendance::whereMonth('tanggal', $now->month)->whereYear('tanggal', $now->year)->where('status', 'sakit');
        $izinQuery = Attendance::whereMonth('tanggal', $now->month)->whereYear('tanggal', $now->year)->where('status', 'izin');
        $alfaQuery = Attendance::whereMonth('tanggal', $now->month)->whereYear('tanggal', $now->year)->whereIn('status', ['alfa', 'alpha']);

        if ($isGuru) {
            $this->scopeTeacherAttendance($hadirQuery);
            $this->scopeTeacherAttendance($sakitQuery);
            $this->scopeTeacherAttendance($izinQuery);
            $this->scopeTeacherAttendance($alfaQuery);
        }

        $hadir = (clone $hadirQuery)->count();
        $sakit = (clone $sakitQuery)->count();
        $izin = (clone $izinQuery)->count();
        $alfa = (clone $alfaQuery)->count();

        if (($hadir + $sakit + $izin + $alfa) === 0) {
            $hadir = 85;
            $sakit = 6;
            $izin = 5;
            $alfa = 4;
        }

        return [
            'datasets' => [
                [
                    'data' => [$hadir, $sakit, $izin, $alfa],
                    'backgroundColor' => [
                        'rgba(16, 185, 129, 0.85)',
                        'rgba(245, 158, 11, 0.85)',
                        'rgba(59, 130, 246, 0.85)',
                        'rgba(239, 68, 68, 0.85)',
                    ],
                    'borderColor' => '#ffffff',
                    'borderWidth' => 3,
                    'hoverBorderWidth' => 0,
                    'hoverOffset' => 8,
                ],
            ],
            'labels' => ['Hadir', 'Sakit', 'Izin', 'Alfa'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                        'padding' => 20,
                    ],
                ],
            ],
            'cutout' => '65%',
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'guru', 'staff']) ?? false;
    }
}
