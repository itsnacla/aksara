<?php

namespace App\Filament\Resources\Teachers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class TeachersTable
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
            TextColumn::make('nip')
                ->label('NIP')
                ->searchable()
                ->sortable(),
            TextColumn::make('kode_guru')
                ->label('Kode')
                ->searchable()
                ->sortable(),
            TextColumn::make('user.name')
                ->label('Nama Guru')
                ->searchable()
                ->sortable(),
            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'aktif' => 'success',
                    'mutasi' => 'warning',
                    'pensiun' => 'info',
                    'berhenti' => 'danger',
                    default => 'gray',
                })
                ->sortable(),
            TextColumn::make('subjects.nama_mapel')
                ->label('Mata Pelajaran')
                ->badge()
                ->separator(', '),
            IconColumn::make('user.is_active')
                ->label('Akun Aktif')
                ->boolean(),
            IconColumn::make('is_walikelas')
                ->label('Wali Kelas')
                ->boolean(),
            IconColumn::make('is_kepalasekolah')
                ->label('Kepala Sekolah')
                ->boolean(),
            TextColumn::make('user.email')
                ->label('Email Akun')
                ->description(fn ($record) => $record->user?->username)
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    protected static function getFilters(): array
    {
        return [
            \Filament\Tables\Filters\SelectFilter::make('status')
                ->label('Filter Status')
                ->options([
                    'aktif' => 'Aktif',
                    'mutasi' => 'Mutasi',
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

                        if (!empty($data['user_password'])) {
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
