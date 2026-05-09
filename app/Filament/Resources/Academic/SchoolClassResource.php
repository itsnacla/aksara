<?php

namespace App\Filament\Resources\Academic;

use App\Models\SchoolClass;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

class SchoolClassResource extends Resource
{
    protected static ?string $model = SchoolClass::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static string|\UnitEnum|null $navigationGroup = 'Academic & Curriculum';

    protected static ?string $navigationLabel = 'School Classes';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Forms\Components\TextInput::make('nama_kelas')
                ->label('Class Name')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('tingkat')
                ->label('Grade')
                ->required()
                ->placeholder('X / XI / XII'),

            Forms\Components\TextInput::make('jurusan')
                ->label('Major')
                ->placeholder('Science / Social / Vocational'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_kelas')
                    ->label('Class Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tingkat')
                    ->label('Grade')
                    ->sortable(),

                Tables\Columns\TextColumn::make('jurusan')
                    ->label('Major')
                    ->sortable(),

                Tables\Columns\TextColumn::make('students_count')
                    ->label('Total Students')
                    ->counts('students'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Academic\SchoolClassResource\Pages\ListSchoolClasses::route('/'),
            'create' => \App\Filament\Resources\Academic\SchoolClassResource\Pages\CreateSchoolClass::route('/create'),
            'edit' => \App\Filament\Resources\Academic\SchoolClassResource\Pages\EditSchoolClass::route('/{record}/edit'),
        ];
    }
}