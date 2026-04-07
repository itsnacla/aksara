<?php

namespace App\Filament\Resources\Teachers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

class TeachersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama_guru')
                    ->label('Nama Guru')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('spesialisasi')
                    ->label('Spesialisasi'),
                IconColumn::make('is_walikelas')
                    ->label('Wali Kelas')
                    ->boolean(),
                TextColumn::make('user.email')
                    ->label('Email Akun')
                    ->description(fn ($record) => $record->user?->username),
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
