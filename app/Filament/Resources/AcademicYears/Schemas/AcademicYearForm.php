<?php

namespace App\Filament\Resources\AcademicYears\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AcademicYearForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tahun_ajaran')
                    ->required()
                    ->maxLength(9)
                    ->placeholder('2025/2026'),
                Select::make('semester')
                    ->options([
                        'ganjil' => 'Ganjil',
                        'genap' => 'Genap',
                    ])
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
