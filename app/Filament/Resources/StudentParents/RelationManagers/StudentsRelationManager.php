<?php

namespace App\Filament\Resources\StudentParents\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    protected static ?string $recordTitleAttribute = 'nisn';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nisn')
                    ->required()
                    ->maxLength(10),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nisn')
                    ->label('NISN'),
                TextColumn::make('user.name')
                    ->label('Nama Siswa'),
                TextColumn::make('classroom.nama_kelas')
                    ->label('Kelas'),
            ]);
    }
}
