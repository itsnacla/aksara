<?php

namespace App\Filament\Resources\Schedules\Pages;

use App\Filament\Resources\Schedules\ScheduleResource;
use App\Models\StudyGroup;
use App\Services\Academic\ScheduleGeneratorService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Grid;

class ListSchedules extends ListRecords
{
    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate_jadwal')
                ->label('Auto-Generate Jadwal')
                ->icon('heroicon-o-sparkles')
                ->color('success')
                ->form([
                    Select::make('study_group_ids')
                        ->label('Pilih Rombel')
                        ->options(fn () => StudyGroup::whereHas('academicYear', fn ($q) => $q->where('is_active', true))->pluck('nama_rombel', 'id')->toArray())
                        ->multiple()
                        ->required()
                        ->searchable()
                        ->hint('Anda bisa memilih lebih dari satu rombel'),
                    Toggle::make('overwrite')
                        ->label('Hapus Jadwal Lama Rombel Ini?')
                        ->default(false),
                ])
                ->action(fn (array $data) => $this->autoGenerateSchedule($data)),
            Action::make('cetak_jadwal')
                ->label('Cetak Jadwal')
                ->icon('heroicon-o-printer')
                ->color('warning')
                ->form([
                    Grid::make(2)
                        ->schema([
                            Radio::make('print_mode')
                                ->label('Mode Cetak')
                                ->options([
                                    'global' => 'Global (Semua Rombel)',
                                    'single' => 'Satuan (Per Rombel)',
                                ])
                                ->default('single')
                                ->live(),
                            Select::make('study_group_id')
                                ->label('Pilih Rombel')
                                ->options(StudyGroup::all()->pluck('nama_rombel', 'id'))
                                ->placeholder('Pilih Rombel...')
                                ->searchable()
                                ->visible(fn ($get) => $get('print_mode') === 'single')
                                ->required(fn ($get) => $get('print_mode') === 'single'),
                        ]),
                    Grid::make(3)
                        ->schema([
                            Select::make('paper_size')
                                ->label('Ukuran Kertas')
                                ->options([
                                    'a4' => 'A4 (210 x 297 mm)',
                                    'f4' => 'F4 / Folio (215 x 330 mm)',
                                ])
                                ->default('a4')
                                ->required(),
                            Select::make('orientation')
                                ->label('Orientasi')
                                ->options([
                                    'portrait' => 'Portrait (Tegak)',
                                    'landscape' => 'Landscape (Miring)',
                                ])
                                ->default('landscape')
                                ->required(),
                            Toggle::make('show_subject_code')
                                ->label('Tampilkan Kode Mapel')
                                ->default(true),
                            Toggle::make('show_teacher_code')
                                ->label('Tampilkan Kode Guru')
                                ->default(true),
                        ]),
                ])
                ->action(function (array $data, ListSchedules $livewire) {
                    $url = route('reports.schedule', [
                        'study_group_id' => $data['print_mode'] === 'global' ? 'all' : $data['study_group_id'],
                        'show_subject_code' => $data['show_subject_code'] ? 1 : 0,
                        'show_teacher_code' => $data['show_teacher_code'] ? 1 : 0,
                        'paper_size' => $data['paper_size'] ?? 'a4',
                        'orientation' => $data['orientation'] ?? 'landscape',
                    ]);

                    $livewire->js("window.open('{$url}', '_blank');");
                }),
            CreateAction::make(),
        ];
    }

    protected function autoGenerateSchedule(array $data): void
    {
        app(ScheduleGeneratorService::class)->generate(
            $data['study_group_ids'],
            $data['overwrite'] ?? false,
        );

        Notification::make()
            ->title('Jadwal Berhasil di-Generate Otomatis!')
            ->success()
            ->send();
    }
}
