<?php

namespace App\Filament\Resources\P5Project\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;
use App\Models\AcademicYear;

class P5ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('academic_year_id')
                    ->default(fn() => AcademicYear::where('is_active', true)->first()?->id),
                
                Select::make('p5_theme_id')
                    ->relationship('theme', 'name', modifyQueryUsing: function ($query) {
                        $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
                        if ($activeYearId) {
                            $query->where('academic_year_id', $activeYearId);
                        }
                    })
                    ->label('Tema Kokurikuler')
                    ->required()
                    ->preload()
                    ->searchable(),

                Select::make('levels')
                    ->relationship('levels', 'nama_tingkatan')
                    ->label('Tingkat')
                    ->multiple()
                    ->required()
                    ->preload()
                    ->searchable(),

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
                        $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
                        if (!$activeYear) {
                            return [];
                        }
                        
                        $options = [];
                        $profiles = \App\Models\GraduateProfile::where('academic_year_id', $activeYear->id)
                            ->with('subdimensions')
                            ->get();
                        
                        foreach ($profiles as $profile) {
                            foreach ($profile->subdimensions as $subdimension) {
                                $key = "{$profile->dimensi}: {$subdimension->subdimensi}";
                                $options[$key] = "{$profile->dimensi} - {$subdimension->subdimensi}";
                            }
                        }
                        
                        return $options;
                    })
                    ->preload()
                    ->searchable()
                    ->columnSpanFull(),
            ]);
    }
}
