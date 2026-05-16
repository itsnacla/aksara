<?php

namespace App\Filament\Resources\LearningObjectiveResource\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LearningObjectiveForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('subject_id')
                ->relationship('subject', 'nama_mapel')
                ->label('Mata Pelajaran')
                ->searchable()
                ->required()
                ->native(false),
                
            Select::make('level_id')
                ->relationship('level', 'nama_tingkatan')
                ->label('Tingkatan / Fase')
                ->required()
                ->native(false),
                
            TextInput::make('code')
                ->label('Kode TP')
                ->placeholder('Contoh: TP 1.1')
                ->maxLength(20),
                
            Toggle::make('is_active')
                ->label('Status Aktif')
                ->default(true),
                
            Textarea::make('description')
                ->label('Deskripsi Tujuan Pembelajaran')
                ->placeholder('Contoh: Menjelaskan proses fotosintesis pada tumbuhan secara sederhana.')
                ->required()
                ->columnSpanFull()
                ->rows(3),
        ]);
    }
}
