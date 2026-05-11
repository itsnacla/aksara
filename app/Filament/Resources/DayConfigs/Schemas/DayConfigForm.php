<?php

namespace App\Filament\Resources\DayConfigs\Schemas;

use App\Models\AcademicYear;
use App\Models\Level;
use App\Models\Subject;
use App\Models\TimeSlot;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;

class DayConfigForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('Konfigurasi Dasar')
                    ->schema([
                        Select::make('academic_year_id')
                            ->label('Tahun Ajaran')
                            ->options(fn() => AcademicYear::where('is_active', true)->pluck('tahun_ajaran', 'id'))
                            ->default(fn() => AcademicYear::where('is_active', true)->first()?->id)
                            ->required(),
                        Select::make('day')
                            ->label('Hari')
                            ->options([
                                'Senin' => 'Senin',
                                'Selasa' => 'Selasa',
                                'Rabu' => 'Rabu',
                                'Kamis' => 'Kamis',
                                'Jumat' => 'Jumat',
                                'Sabtu' => 'Sabtu',
                            ])
                            ->required(),
                        Select::make('level_ids')
                            ->label('Tingkatan (Level)')
                            ->options(fn() => Level::pluck('nama_tingkatan', 'id'))
                            ->multiple()
                            ->required()
                            ->searchable(),
                        Toggle::make('is_closed')
                            ->label('Hari Libur')
                            ->helperText('Jika aktif, generator tidak akan mengisi jadwal di hari ini untuk level yang dipilih.')
                            ->default(false)
                            ->live(),
                        Select::make('max_time_slot_id')
                            ->label('Batas Jam Maksimal')
                            ->options(fn() => TimeSlot::pluck('nama_jam', 'id'))
                            ->hint('Jam terakhir yang diperbolehkan')
                            ->searchable()
                            ->hidden(fn (Get $get) => $get('is_closed')),
                    ])
                    ->columns(1), // Ubah jadi 1 kolom (atas-bawah)

                Fieldset::make('Aturan Mata Pelajaran Wajib')
                    ->schema([
                        Select::make('mandatory_subject_id')
                            ->label('Mata Pelajaran Wajib')
                            ->options(fn() => Subject::pluck('nama_mapel', 'id'))
                            ->searchable(),
                        Select::make('mandatory_time_slot_id')
                            ->label('Pada Jam Ke-')
                            ->options(fn() => TimeSlot::pluck('nama_jam', 'id'))
                            ->searchable(),
                    ])
                    ->columns(1) // Ubah jadi 1 kolom (atas-bawah)
                    ->hidden(fn (Get $get) => $get('is_closed')),
            ]);
    }
}
