<?php

namespace App\Filament\Resources\Students\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nisn')
                    ->label('NISN')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama_siswa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('classroom.nama_kelas')
                    ->label('Kelas')
                    ->badge()
                    ->sortable(),
                TextColumn::make('parent.nama_wali')
                    ->label('Orang Tua')
                    ->searchable(),
                TextColumn::make('user.email')
                    ->label('Email Akun')
                    ->description(fn ($record) => $record->user?->username)
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
