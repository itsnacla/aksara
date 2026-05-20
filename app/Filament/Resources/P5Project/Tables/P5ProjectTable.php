<?php

namespace App\Filament\Resources\P5Project\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class P5ProjectTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Judul Kegiatan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('theme.name')
                    ->label('Tema')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('levels.nama_tingkatan')
                    ->label('Tingkat')
                    ->badge()
                    ->searchable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('p5_theme_id')
                    ->relationship('theme', 'name')
                    ->label('Tema'),
                \Filament\Tables\Filters\SelectFilter::make('levels')
                    ->relationship('levels', 'nama_tingkatan')
                    ->label('Tingkat'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
