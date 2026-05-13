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
                \Filament\Forms\Components\Toggle::make('is_last_level')
                    ->label('Tingkatan Terakhir?')
                    ->helperText('Jika aktif, siswa yang naik dari tingkat ini akan otomatis berstatus Lulus.')
                    ->default(false),
            ]);
    }
}
