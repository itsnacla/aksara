<?php

namespace App\Filament\Resources\Classrooms\Schemas;

use App\Models\AcademicYear;
use App\Models\Level;
use App\Models\Teacher;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class ClassroomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->relationship('academicYear', 'tahun_ajaran')
                    ->required()
                    ->searchable(),
                Select::make('level_id')
                    ->label('Tingkatan/Level')
                    ->relationship('level', 'nama_tingkatan')
                    ->required()
                    ->searchable(),
                TextInput::make('nama_kelas')
                    ->required()
                    ->maxLength(50)
                    ->placeholder('X-IPA-1'),
                Select::make('walikelas_id')
                    ->label('Wali Kelas')
                    ->options(fn () => \App\Models\Teacher::with('user')->where('is_walikelas', true)->get()->pluck('user.name', 'id'))
                    ->required()
                    ->searchable(),
            ]);
    }
}
