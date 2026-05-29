<?php

namespace App\Filament\Resources\AcademicYears\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class AcademicYearForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tahun_ajaran')
                    ->required()
                    ->maxLength(9)
                    ->placeholder('2025/2026')
                    ->unique(
                        table: 'academic_years',
                        column: 'tahun_ajaran',
                        ignoreRecord: true,
                        modifyRuleUsing: function ($rule, $get) {
                            return $rule->where('semester', $get('semester'));
                        }
                    )
                    ->validationMessages([
                        'unique' => 'Tahun ajaran dengan semester ini sudah ada.',
                    ]),
                Select::make('semester')
                    ->options([
                        'ganjil' => 'Ganjil',
                        'genap' => 'Genap',
                    ])
                    ->required()
                    ->live(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
