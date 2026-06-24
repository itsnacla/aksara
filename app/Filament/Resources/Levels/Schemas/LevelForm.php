<?php

namespace App\Filament\Resources\Levels\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LevelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_tingkatan')
                    ->required()
                    ->maxLength(50),
                \Filament\Forms\Components\Select::make('fase')
                    ->label('Fase (Kurikulum Merdeka)')
                    ->options([
                        'Fondasi' => 'Fase Fondasi (PAUD/TK)',
                        'A' => 'Fase A (Kelas 1-2)',
                        'B' => 'Fase B (Kelas 3-4)',
                        'C' => 'Fase C (Kelas 5-6)',
                        'D' => 'Fase D (SMP 7-9)',
                        'E' => 'Fase E (SMA 10)',
                        'F' => 'Fase F (SMA 11-12)',
                    ])
                    ->required()
                    ->placeholder('Pilih Fase...'),
                \Filament\Forms\Components\Toggle::make('is_last_level')
                    ->label('Tingkatan Terakhir?')
                    ->helperText('Jika aktif, siswa yang naik dari tingkat ini akan otomatis berstatus Lulus.')
                    ->default(false),
            ]);
    }
}
