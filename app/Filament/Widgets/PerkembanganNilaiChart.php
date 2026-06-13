<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\StudyGroup;
use App\Models\Student;
use App\Services\Academic\GradeProgressBuilder;
use Livewire\Attributes\On;

class PerkembanganNilaiChart extends ChartWidget
{
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
        $colors = ['#3b1b61', '#d93836', '#4a237a', '#c0392b', '#5b2c6f', '#e74c3c', '#8e44ad'];
        
        $colorIndex = 0;
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
            } else {
                $dataset['backgroundColor'] = $colors[$colorIndex % count($colors)];
                $colorIndex++;
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
