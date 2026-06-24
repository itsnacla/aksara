<?php

namespace App\Filament\Resources\Levels\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LevelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_tingkatan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fase')
                    ->label('Fase')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (string $state): string => "Fase {$state}")
                    ->sortable(),
                \Filament\Tables\Columns\IconColumn::make('is_last_level')
                    ->label('Tingkat Terakhir')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
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
