<?php

namespace App\Filament\Resources\StudentParents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StudentParentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama Wali')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('hubungan')
                    ->badge()
                    ->color('info'),
                TextColumn::make('no_whatsapp')
                    ->label('WhatsApp'),
                TextColumn::make('user.email')
                    ->label('Akun User'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
