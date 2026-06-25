<?php

namespace App\Filament\Resources\P5Theme\Schemas;

use App\Models\AcademicYear;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class P5ThemeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('academic_year_id')
                    ->default(fn () => AcademicYear::where('is_active', true)->first()?->id),
                TextInput::make('name')
                    ->label('Nama Tema P5')
                    ->required()
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true),
            ]);
    }
}
