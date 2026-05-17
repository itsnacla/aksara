<?php

namespace App\Filament\Resources\Subjects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_mapel')
                    ->label('Mata Pelajaran')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subjectReportGroup.nama_kelompok')
                    ->label('Kelompok Rapor')
                    ->badge()
                    ->color('primary')
                    ->placeholder('-')
                    ->sortable()
                    ->searchable(),
                IconColumn::make('is_graded')
                    ->label('Ikut Rapor')
                    ->boolean()
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('kode_mapel')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('kkm')
                    ->label('KKM')
                    ->sortable(),
                TextColumn::make('total_jp')
                    ->label('Total JP')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make()->modal(),
                EditAction::make()->modal(),
                DeleteAction::make()->modal(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
