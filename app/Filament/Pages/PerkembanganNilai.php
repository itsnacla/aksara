<?php

namespace App\Filament\Pages;

use App\Models\StudyGroup;
use App\Models\Student;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Pages\Page;
use Filament\Forms\Get;

class PerkembanganNilai extends Page implements HasForms
{
    use InteractsWithForms;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-presentation-chart-bar';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Akademik & KBM';
    }

    public static function getNavigationLabel(): string
    {
        return 'Perkembangan Nilai';
    }

    public function getTitle(): \Illuminate\Contracts\Support\Htmlable|string
    {
        return 'Grafik Perkembangan Rerata Nilai Rapor Siswa';
    }

    public static function getNavigationSort(): ?int
    {
        return 10;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('View:PerkembanganNilai') ?? false;
    }

    protected string $view = 'filament.pages.perkembangan-nilai';

    public ?int $study_group_id = null;
    public ?string $student_id = 'all';

    public function mount()
    {
        $query = StudyGroup::with('academicYear')->whereHas('academicYear', function ($q) {
            $q->where('is_active', true);
        });

        $user = auth()->user();
        if ($user && $user->hasRole('guru') && !$user->hasAnyRole(['super_admin', 'staff']) && $user->teacher) {
            $teacherId = $user->teacher->id;
            $query->where(function ($q) use ($teacherId) {
                $q->where('walikelas_id', $teacherId)
                  ->orWhereHas('schedules', fn ($sq) => $sq->where('teacher_id', $teacherId));
            });
        }

        $firstGroup = $query->first();

        if ($firstGroup) {
            $this->form->fill([
                'study_group_id' => $firstGroup->id,
                'student_id' => 'all',
            ]);
        } else {
            $this->form->fill();
        }
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('study_group_id')
                    ->label('Pilih Kelas')
                    ->options(function () {
                        $query = StudyGroup::whereHas('academicYear', function ($q) {
                            $q->where('is_active', true);
                        })->with('academicYear');

                        $user = auth()->user();
                        if ($user && $user->hasRole('guru') && !$user->hasAnyRole(['super_admin', 'staff']) && $user->teacher) {
                            $teacherId = $user->teacher->id;
                            $query->where(function ($q) use ($teacherId) {
                                $q->where('walikelas_id', $teacherId)
                                  ->orWhereHas('schedules', fn ($sq) => $sq->where('teacher_id', $teacherId));
                            });
                        }

                        return $query->get()->mapWithKeys(function ($sg) {
                            return [$sg->id => $sg->nama_rombel . ' (' . ($sg->academicYear ? $sg->academicYear->tahun_ajaran : '-') . ')'];
                        });
                    })
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function ($set, $state) {
                        $set('student_id', 'all');
                        $this->dispatch('filter-updated', studyGroupId: $state, studentId: 'all');
                    }),

                Select::make('student_id')
                    ->label('Pilih Siswa')
                    ->options(function ($get) {
                        $sgId = $get('study_group_id');
                        if (!$sgId) {
                            return ['all' => 'SEMUA SISWA'];
                        }
                        
                        $sg = StudyGroup::with('students.user')->find($sgId);
                        if (!$sg) return ['all' => 'SEMUA SISWA'];

                        $options = ['all' => 'SEMUA SISWA'];
                        foreach ($sg->students as $student) {
                            $options[$student->id] = $student->user->name ?? $student->nis;
                        }
                        return $options;
                    })
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function ($state, $get) {
                        $this->dispatch('filter-updated', studyGroupId: $get('study_group_id'), studentId: $state);
                    }),
            ])
            ->columns(1);
    }
}
