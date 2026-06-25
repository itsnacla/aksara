<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(static::getColumns())
            ->filters(static::getFilters())
            ->actions(static::getActions())
            ->bulkActions(static::getBulkActions());
    }

    protected static function getColumns(): array
    {
        return [
            TextColumn::make('name')
                ->searchable(),
            TextColumn::make('username')
                ->searchable(),
            TextColumn::make('email')
                ->label('Email Address')
                ->searchable(),
            TextColumn::make('roles.name')
                ->label('Role')
                ->badge()
                ->searchable(),
            TextColumn::make('created_at')
                ->label('Created At')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    protected static function getFilters(): array
    {
        return [
            //
        ];
    }

    protected static function getActions(): array
    {
        return [
            ViewAction::make()->modal(),
            EditAction::make()->modal(),
            DeleteAction::make()->modal(),
        ];
    }

    protected static function getBulkActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ];
    }
}
