<?php

namespace App\Filament\Resources\P5ProjectResource\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TagsInput;
use Filament\Schemas\Schema;
use App\Models\AcademicYear;

class P5ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('p5_theme_id')
                    ->relationship('theme', 'name')
                    ->label('Tema Kokurikuler')
                    ->required()
                    ->searchable(),
                
                Select::make('academic_year_id')
                    ->relationship('academicYear', 'name')
                    ->label('Tahun Ajaran')
                    ->default(fn() => AcademicYear::where('is_active', true)->first()?->id)
                    ->required()
                    ->searchable(),

                Select::make('fase')
                    ->label('Fase')
                    ->options([
                        'A' => 'Fase A (Kelas 1-2)',
                        'B' => 'Fase B (Kelas 3-4)',
                        'C' => 'Fase C (Kelas 5-6)',
                        'D' => 'Fase D (Kelas 7-9)',
                        'E' => 'Fase E (Kelas 10)',
                        'F' => 'Fase F (Kelas 11-12)',
                    ])
                    ->required(),

                TextInput::make('name')
                    ->label('Judul Kegiatan Kokurikuler')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Textarea::make('target_description')
                    ->label('Tujuan Akhir Kegiatan Kokurikuler')
                    ->rows(3)
                    ->columnSpanFull(),

                Select::make('graduate_profile')
                    ->label('Profil Lulusan (Dimensi/Elemen)')
                    ->multiple()
                    ->options(function () {
                        return \App\Models\GraduateProfile::all()
                            ->mapWithKeys(fn($item) => ["{$item->dimensi}: {$item->subdimensi}" => "{$item->dimensi} - {$item->subdimensi}"])
                            ->toArray();
                    })
                    ->preload()
                    ->searchable()
                    ->columnSpanFull(),
            ]);
    }
}
