<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ScopesToTeacherStudents;
use App\Models\AcademicYear;
use App\Models\StudyGroup;
use Filament\Widgets\ChartWidget;

class StudentDistributionChart extends ChartWidget
{
    use ScopesToTeacherStudents;

    protected ?string $heading = 'Distribusi Siswa per Rombel';

    protected ?string $description = 'Jumlah siswa di setiap rombongan belajar';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
    ];

    protected ?string $maxHeight = '320px';

    protected function getData(): array
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $isGuru = auth()->user()?->hasRole('guru');

        if ($isGuru) {
            $studyGroups = $this->getTeacherStudyGroups();

            if ($studyGroups->isEmpty()) {
                return [
                    'datasets' => [
                        [
                            'label' => 'Jumlah Siswa',
                            'data' => [],
                            'backgroundColor' => [],
                            'borderColor' => [],
                            'borderWidth' => 2,
                            'borderRadius' => 8,
                        ],
                    ],
                    'labels' => ['Belum ada rombel'],
                ];
            }

            $studyGroups = StudyGroup::whereIn('id', $studyGroups->pluck('id'))
                ->withCount('students')
                ->orderBy('nama_rombel')
                ->get();

            $colors = [
                'rgba(0, 93, 167, 0.8)',
                'rgba(16, 185, 129, 0.8)',
                'rgba(245, 158, 11, 0.8)',
                'rgba(139, 92, 246, 0.8)',
                'rgba(236, 72, 153, 0.8)',
                'rgba(59, 130, 246, 0.8)',
            ];
            $borderColors = ['#005da7', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#3b82f6'];

            return [
                'datasets' => [
                    [
                        'label' => 'Jumlah Siswa',
                        'data' => $studyGroups->pluck('students_count')->toArray(),
                        'backgroundColor' => array_slice($colors, 0, $studyGroups->count()),
                        'borderColor' => array_slice($borderColors, 0, $studyGroups->count()),
                        'borderWidth' => 2,
                        'borderRadius' => 8,
                    ],
                ],
                'labels' => $studyGroups->pluck('nama_rombel')->toArray(),
            ];
        }

        $studyGroups = StudyGroup::query()
            ->when($activeYear, fn ($q) => $q->where('academic_year_id', $activeYear->id))
            ->withCount('students')
            ->orderBy('nama_rombel')
            ->get();

        if ($studyGroups->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'label' => 'Jumlah Siswa',
                        'data' => [25, 28, 30, 27, 26, 29],
                        'backgroundColor' => [
                            'rgba(0, 93, 167, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(139, 92, 246, 0.8)',
                            'rgba(236, 72, 153, 0.8)',
                            'rgba(59, 130, 246, 0.8)',
                        ],
                        'borderColor' => [
                            '#005da7',
                            '#10b981',
                            '#f59e0b',
                            '#8b5cf6',
                            '#ec4899',
                            '#3b82f6',
                        ],
                        'borderWidth' => 2,
                        'borderRadius' => 8,
                    ],
                ],
                'labels' => ['Kelas 1A', 'Kelas 1B', 'Kelas 2A', 'Kelas 2B', 'Kelas 3A', 'Kelas 3B'],
            ];
        }

        $colors = [
            'rgba(0, 93, 167, 0.8)',
            'rgba(16, 185, 129, 0.8)',
            'rgba(245, 158, 11, 0.8)',
            'rgba(139, 92, 246, 0.8)',
            'rgba(236, 72, 153, 0.8)',
            'rgba(59, 130, 246, 0.8)',
            'rgba(20, 184, 166, 0.8)',
            'rgba(249, 115, 22, 0.8)',
            'rgba(168, 85, 247, 0.8)',
            'rgba(244, 63, 94, 0.8)',
        ];

        $borderColors = [
            '#005da7', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899',
            '#3b82f6', '#14b8a6', '#f97316', '#a855f7', '#f43f5e',
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Siswa',
                    'data' => $studyGroups->pluck('students_count')->toArray(),
                    'backgroundColor' => array_slice($colors, 0, $studyGroups->count()),
                    'borderColor' => array_slice($borderColors, 0, $studyGroups->count()),
                    'borderWidth' => 2,
                    'borderRadius' => 8,
                ],
            ],
            'labels' => $studyGroups->pluck('nama_rombel')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'color' => 'rgba(0, 0, 0, 0.04)',
                    ],
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
        ];
    }

    public static function canView(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        if ($user->hasRole('guru')) {
            return false;
        }

        return $user->hasAnyRole(['super_admin', 'staff']);
    }
}
