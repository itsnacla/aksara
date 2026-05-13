<?php

namespace App\Filament\Widgets;

use App\Models\Grade;
use App\Models\AcademicYear;
use Filament\Widgets\ChartWidget;

class GradeDistributionChart extends ChartWidget
{
    protected ?string $heading = 'Distribusi Nilai Rata-rata Siswa';

    protected ?string $description = 'Persentase siswa berdasarkan rentang nilai';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'md' => 1,
    ];

    protected ?string $maxHeight = '320px';

    protected function getData(): array
    {
        $activeYear = AcademicYear::where('is_active', true)->first();

        $grades = Grade::query()
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->get();

        if ($grades->isEmpty()) {
            // Demo data
            return [
                'datasets' => [
                    [
                        'data' => [15, 35, 30, 15, 5],
                        'backgroundColor' => [
                            'rgba(16, 185, 129, 0.85)',
                            'rgba(59, 130, 246, 0.85)',
                            'rgba(245, 158, 11, 0.85)',
                            'rgba(249, 115, 22, 0.85)',
                            'rgba(239, 68, 68, 0.85)',
                        ],
                        'borderColor' => '#ffffff',
                        'borderWidth' => 3,
                        'hoverOffset' => 8,
                    ],
                ],
                'labels' => ['A (90-100)', 'B (80-89)', 'C (70-79)', 'D (60-69)', 'E (<60)'],
            ];
        }

        // Calculate average for each student
        $ranges = ['A (90-100)' => 0, 'B (80-89)' => 0, 'C (70-79)' => 0, 'D (60-69)' => 0, 'E (<60)' => 0];

        $studentGrades = $grades->groupBy('student_id');

        foreach ($studentGrades as $studentId => $gradeSet) {
            $avg = $gradeSet->avg(function ($grade) {
                return ($grade->nilai_tugas + $grade->nilai_uts + $grade->nilai_uas) / 3;
            });

            if ($avg >= 90) $ranges['A (90-100)']++;
            elseif ($avg >= 80) $ranges['B (80-89)']++;
            elseif ($avg >= 70) $ranges['C (70-79)']++;
            elseif ($avg >= 60) $ranges['D (60-69)']++;
            else $ranges['E (<60)']++;
        }

        return [
            'datasets' => [
                [
                    'data' => array_values($ranges),
                    'backgroundColor' => [
                        'rgba(16, 185, 129, 0.85)',
                        'rgba(59, 130, 246, 0.85)',
                        'rgba(245, 158, 11, 0.85)',
                        'rgba(249, 115, 22, 0.85)',
                        'rgba(239, 68, 68, 0.85)',
                    ],
                    'borderColor' => '#ffffff',
                    'borderWidth' => 3,
                    'hoverOffset' => 8,
                ],
            ],
            'labels' => array_keys($ranges),
        ];
    }

    protected function getType(): string
    {
        return 'polarArea';
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
                        'padding' => 16,
                    ],
                ],
            ],
            'scales' => [
                'r' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'color' => 'rgba(0, 0, 0, 0.04)',
                    ],
                    'ticks' => [
                        'display' => false,
                    ],
                ],
            ],
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }
}
