<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\StudyGroup;
use App\Models\Student;
use App\Services\Academic\GradeProgressBuilder;
use Livewire\Attributes\On;

class PerkembanganNilaiChart extends ChartWidget
{
    protected static bool $isDiscovered = false;

    public ?int $studyGroupId = null;
    public ?string $studentId = 'all';

    protected ?string $heading = 'Riwayat Nilai Rapor';
    protected ?string $maxHeight = '400px';

    #[On('filter-updated')]
    public function updateFilters($studyGroupId, $studentId)
    {
        $this->studyGroupId = $studyGroupId;
        $this->studentId = $studentId;
    }

    public function getHeading(): string
    {
        if (!$this->studyGroupId) {
            return 'Pilih Kelas Terlebih Dahulu';
        }

        $sg = StudyGroup::find($this->studyGroupId);
        if (!$sg) return 'Riwayat Nilai Rapor';

        if ($this->studentId === 'all') {
            return 'Riwayat Nilai Rapor Kelas ' . $sg->nama_rombel;
        }

        $student = Student::with('user')->find($this->studentId);
        return 'Riwayat Nilai Rapor ' . ($student->user->name ?? '');
    }

    protected function getData(): array
    {
        if (!$this->studyGroupId) {
            return ['datasets' => [], 'labels' => []];
        }

        $sg = StudyGroup::find($this->studyGroupId);
        if (!$sg) return ['datasets' => [], 'labels' => []];

        $student = null;
        if ($this->studentId && $this->studentId !== 'all') {
            $student = Student::find($this->studentId);
        }

        $builder = new GradeProgressBuilder();
        $data = $builder->build($sg, $student);

        // Map data to Chart.js format
        $datasets = [];
        
        $scheduleColorsLight = ['#fee2e2', '#ffedd5', '#fef9c3', '#dcfce7', '#d1fae5', '#e0f2fe', '#e0e7ff', '#f3e8ff', '#fae8ff'];
        $scheduleColorsDark  = ['#ef4444', '#f97316', '#eab308', '#22c55e', '#10b981', '#0ea5e9', '#6366f1', '#a855f7', '#d946ef'];
        
        $subjectNames = $data['chart']['subject_names'] ?? [];
        $barBackgroundColors = [];
        $barBorderColors = [];
        
        foreach ($subjectNames as $namaMapel) {
            $colorIndex = crc32($namaMapel) % count($scheduleColorsLight);
            $barBackgroundColors[] = $scheduleColorsLight[abs($colorIndex)];
            $barBorderColors[] = $scheduleColorsDark[abs($colorIndex)];
        }
        
        foreach ($data['chart']['series'] as $series) {
            $isLine = $series['name'] === 'Rata-Rata' || $series['type'] === 'line';
            
            $dataset = [
                'label' => $series['name'],
                'data' => $series['data'],
                'type' => $isLine ? 'line' : 'bar',
            ];
            
            if ($isLine) {
                $dataset['borderColor'] = '#c0392b';
                $dataset['borderWidth'] = 4;
                $dataset['fill'] = false;
                $dataset['tension'] = 0.4; // smooth curve
                $dataset['order'] = 1;
            } else {
                $dataset['backgroundColor'] = empty($barBackgroundColors) ? '#8e44ad' : $barBackgroundColors;
                $dataset['borderColor'] = empty($barBorderColors) ? '#5b2c6f' : $barBorderColors;
                $dataset['borderWidth'] = 2;
                $dataset['order'] = 2;
                $dataset['borderRadius'] = 4;
            }
            
            $datasets[] = $dataset;
        }

        return [
            'datasets' => $datasets,
            'labels' => $data['chart']['categories'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'min' => 0,
                    'max' => 100,
                    'title' => [
                        'display' => true,
                        'text' => 'Capaian Nilai',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
