<?php

namespace App\Filament\Resources\Classrooms\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ClassroomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_ruangan')
                    ->label('Nama Ruangan')
                    ->placeholder('Ruang 10-A / Lab Komputer')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
