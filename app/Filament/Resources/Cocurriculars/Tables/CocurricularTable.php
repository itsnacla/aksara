<?php

namespace App\Filament\Resources\Cocurriculars\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CocurricularTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tema')
                    ->label('Tema')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('nama_projek')
                    ->label('Projek')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('fase')
                    ->label('Fase')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                    
                TextColumn::make('tahun_ajaran')
                    ->label('TA')
                    ->sortable(),
                    
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
