<?php

namespace App\Filament\Resources\TimeSlots\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TimeSlotsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('urutan')
                    ->label('No')
                    ->sortable(),
                TextColumn::make('nama_jam')
                    ->label('Jam Pelajaran')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('waktu_mulai')
                    ->label('Mulai')
                    ->time('H:i'),
                TextColumn::make('waktu_selesai')
                    ->label('Selesai')
                    ->time('H:i'),
                TextColumn::make('levels.nama_tingkatan')
                    ->label('Tingkatan')
                    ->badge()
                    ->separator(', '),
                ToggleColumn::make('is_istirahat')
                    ->label('Istirahat'),
            ])
            ->defaultSort('urutan')
            ->filters([
                SelectFilter::make('levels')
                    ->label('Filter Tingkatan')
                    ->relationship('levels', 'nama_tingkatan'),
            ])
            ->actions([
                ViewAction::make()
                    ->modal(),
                EditAction::make()
                    ->modal(),
                DeleteAction::make()
                    ->modal(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
