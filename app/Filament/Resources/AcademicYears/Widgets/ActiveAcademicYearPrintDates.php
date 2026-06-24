<?php

namespace App\Filament\Resources\AcademicYears\Widgets;

use App\Models\AcademicYear;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Livewire\Attributes\On;

class ActiveAcademicYearPrintDates extends Widget implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.widgets.active-academic-year-print-dates';
    protected int | string | array $columnSpan = 'full';

    public ?array $data = [];

    #[On('active-academic-year-changed')]
    public function loadDates(): void
    {
        $this->data = []; // Clear current state
        $activeYear = AcademicYear::where('is_active', true)->first();
        if ($activeYear) {
            $this->form->fill([
                'rapor_date' => $activeYear->rapor_date,
                'schedule_date' => $activeYear->schedule_date,
                'attendance_date' => $activeYear->attendance_date,
                'pelengkap_rapor_date' => $activeYear->pelengkap_rapor_date,
            ]);
        } else {
            $this->form->fill([]);
        }
    }

    public function mount(): void
    {
        $this->loadDates();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                DatePicker::make('rapor_date')
                    ->label('Tanggal Rapor')
                    ->required()
                    ->native(false)
                    ->displayFormat('d F Y'),
                DatePicker::make('schedule_date')
                    ->label('Tanggal Jadwal Pelajaran')
                    ->required()
                    ->native(false)
                    ->displayFormat('d F Y'),
                DatePicker::make('attendance_date')
                    ->label('Tanggal Laporan Presensi')
                    ->required()
                    ->native(false)
                    ->displayFormat('d F Y'),
                DatePicker::make('pelengkap_rapor_date')
                    ->label('Tanggal Buku Induk / Pelengkap Rapor')
                    ->required()
                    ->native(false)
                    ->displayFormat('d F Y'),
            ])
            ->columns(4)
            ->statePath('data');
    }

    public function save(): void
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        if ($activeYear) {
            $activeYear->update($this->form->getState());
            Notification::make()
                ->title('Tersimpan')
                ->body('Pengaturan tanggal cetak untuk tahun ajaran ' . $activeYear->tahun_ajaran . ' berhasil disimpan.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Gagal')
                ->body('Tidak ada Tahun Ajaran yang aktif.')
                ->danger()
                ->send();
        }
    }
}
