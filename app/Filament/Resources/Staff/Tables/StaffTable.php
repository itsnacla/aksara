<?php

namespace App\Filament\Resources\Staff\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class StaffTable
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
            TextColumn::make('user.name')
                ->label('Nama Lengkap Staff')
                ->searchable()
                ->sortable(),
            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'aktif' => 'success',
                    'pensiun' => 'info',
                    'berhenti' => 'danger',
                    default => 'gray',
                })
                ->sortable(),
            TextColumn::make('jabatan')
                ->label('Jabatan')
                ->searchable(),
            IconColumn::make('user.is_active')
                ->label('Akun Aktif')
                ->boolean(),
            TextColumn::make('no_whatsapp')
                ->label('No WhatsApp')
                ->searchable(),
            TextColumn::make('user.email')
                ->label('Email Akun')
                ->description(fn ($record) => $record->user?->username)
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    protected static function getFilters(): array
    {
        return [
            SelectFilter::make('status')
                ->label('Filter Status')
                ->options([
                    'aktif' => 'Aktif',
                    'pensiun' => 'Pensiun',
                    'berhenti' => 'Berhenti Kerja',
                ]),
        ];
    }

    protected static function getActions(): array
    {
        return [
            ViewAction::make()
                ->modal()
                ->mutateRecordDataUsing(function (array $data, $record): array {
                    $user = $record->user;
                    if ($user) {
                        $data['user_name'] = $user->name;
                        $data['user_username'] = $user->username;
                        $data['user_email'] = $user->email;
                        $data['user_is_active'] = $user->is_active;
                    }

                    return $data;
                }),
            EditAction::make()
                ->modal()
                ->mutateRecordDataUsing(function (array $data, $record): array {
                    $user = $record->user;
                    if ($user) {
                        $data['user_name'] = $user->name;
                        $data['user_username'] = $user->username;
                        $data['user_email'] = $user->email;
                        $data['user_is_active'] = $user->is_active;
                    }

                    return $data;
                })
                ->mutateFormDataUsing(function (array $data, $record): array {
                    $user = $record->user;
                    if ($user) {
                        $updateData = array_filter([
                            'name' => $data['user_name'] ?? null,
                            'username' => $data['user_username'] ?? null,
                            'email' => $data['user_email'] ?? null,
                            'is_active' => $data['user_is_active'] ?? true,
                        ]);

                        if (! empty($data['user_password'])) {
                            $updateData['password'] = Hash::make($data['user_password']);
                        }

                        $user->update($updateData);
                    }

                    unset($data['user_name'], $data['user_username'], $data['user_email'], $data['user_password'], $data['user_is_active']);

                    return $data;
                }),
            DeleteAction::make()
                ->modal(),
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
