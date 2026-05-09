<?php

namespace App\Filament\Resources\Academic;

use App\Models\Subject;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static string|\UnitEnum|null $navigationGroup = 'Academic & Curriculum';

    protected static ?string $navigationLabel = 'Subjects';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Forms\Components\TextInput::make('nama_mapel')
                ->label('Subject Name')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('kode_mapel')
                ->label('Subject Code')
                ->unique(ignoreRecord: true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_mapel')
                    ->label('Subject Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('kode_mapel')
                    ->label('Code')
                    ->sortable(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Academic\SubjectResource\Pages\ListSubjects::route('/'),
            'create' => \App\Filament\Resources\Academic\SubjectResource\Pages\CreateSubject::route('/create'),
            'edit' => \App\Filament\Resources\Academic\SubjectResource\Pages\EditSubject::route('/{record}/edit'),
        ];
    }
}