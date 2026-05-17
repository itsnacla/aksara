<?php

namespace App\Filament\Resources\P5Group\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class P5GroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Kelompok')
                    ->placeholder('Contoh: 9.1')
                    ->required()
                    ->maxLength(255),

                Select::make('level_id')
                    ->relationship('level', 'nama_tingkatan')
                    ->label('Tingkat')
                    ->required()
                    ->searchable(),

                Select::make('teacher_id')
                    ->options(\App\Models\Teacher::with('user')->get()->pluck('user.name', 'id'))
                    ->label('Koordinator / Fasilitator')
                    ->required()
                    ->searchable(),

                Select::make('p5_project_id')
                    ->relationship('project', 'name')
                    ->label('Kegiatan Kokurikuler (P5)')
                    ->required()
                    ->searchable()
                    ->columnSpanFull(),
            ]);
    }
}
