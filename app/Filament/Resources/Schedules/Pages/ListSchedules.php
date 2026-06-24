<?php

namespace App\Filament\Resources\Schedules\Pages;

use App\Filament\Resources\Schedules\ScheduleResource;
use App\Services\Academic\ScheduleGeneratorService;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Grid;

class ListSchedules extends ListRecords
{
    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('generate_jadwal')
                ->label('Auto-Generate Jadwal')
                ->icon('heroicon-o-sparkles')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\Select::make('study_group_ids')
                        ->label('Pilih Rombel')
                        ->options(fn() => \App\Models\StudyGroup::whereHas('academicYear', fn($q) => $q->where('is_active', true))->pluck('nama_rombel', 'id')->toArray())
                        ->multiple()
                        ->required()
                        ->searchable()
                        ->hint('Anda bisa memilih lebih dari satu rombel'),
                    \Filament\Forms\Components\Toggle::make('overwrite')
                        ->label('Hapus Jadwal Lama Rombel Ini?')
                        ->default(false),
                ])
                ->action(fn (array $data) => $this->autoGenerateSchedule($data)),
            \Filament\Actions\Action::make('cetak_jadwal')
                ->label('Cetak Jadwal')
                ->icon('heroicon-o-printer')
                ->color('warning')
                ->form([
                    Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\Radio::make('print_mode')
                                ->label('Mode Cetak')
                                ->options([
                                    'global' => 'Global (Semua Rombel)',
                                    'single' => 'Satuan (Per Rombel)',
                                ])
                                ->default('single')
                                ->live(),
                            \Filament\Forms\Components\Select::make('study_group_id')
                                ->label('Pilih Rombel')
                                ->options(\App\Models\StudyGroup::all()->pluck('nama_rombel', 'id'))
                                ->placeholder('Pilih Rombel...')
                                ->searchable()
                                ->visible(fn ($get) => $get('print_mode') === 'single')
                                ->required(fn ($get) => $get('print_mode') === 'single'),
                        ]),
                    Grid::make(3)
                        ->schema([
                            \Filament\Forms\Components\Select::make('paper_size')
                                ->label('Ukuran Kertas')
                                ->options([
                                    'a4' => 'A4 (210 x 297 mm)',
                                    'f4' => 'F4 / Folio (215 x 330 mm)',
                                ])
                                ->default('a4')
                                ->required(),
                            \Filament\Forms\Components\Select::make('orientation')
                                ->label('Orientasi')
                                ->options([
                                    'portrait' => 'Portrait (Tegak)',
                                    'landscape' => 'Landscape (Miring)',
                                ])
                                ->default('landscape')
                                ->required(),
                            \Filament\Forms\Components\Toggle::make('show_subject_code')
                                ->label('Tampilkan Kode Mapel')
                                ->default(true),
                            \Filament\Forms\Components\Toggle::make('show_teacher_code')
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

        \Filament\Notifications\Notification::make()
            ->title('Jadwal Berhasil di-Generate Otomatis!')
            ->success()
            ->send();
    }
}
