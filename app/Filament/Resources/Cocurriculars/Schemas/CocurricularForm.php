<?php

namespace App\Filament\Resources\Cocurriculars\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CocurricularForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('tema')
                ->label('Tema Projek P5')
                ->placeholder('Contoh: Gaya Hidup Berkelanjutan')
                ->required(),
                
            TextInput::make('nama_projek')
                ->label('Nama Projek')
                ->placeholder('Contoh: Pengolahan Sampah Plastik')
                ->required(),
                
            Select::make('fase')
                ->label('Fase (Kurikulum Merdeka)')
                ->options(fn () => \App\Models\Level::query()
                    ->whereNotNull('fase')
                    ->distinct()
                    ->orderBy('fase')
                    ->pluck('fase', 'fase')
                    ->toArray())
                ->required()
                ->native(false),
                
            TextInput::make('tahun_ajaran')
                ->label('Tahun Ajaran')
                ->placeholder('2024/2025')
                ->required(),
                
            Textarea::make('deskripsi')
                ->label('Deskripsi Projek')
                ->columnSpanFull()
                ->rows(3),
        ]);
    }
}
