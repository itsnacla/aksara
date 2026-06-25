<?php

namespace App\Filament\Resources\StudentParents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

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
            ->actions([
                ViewAction::make()
                    ->modal()
                    ->modalWidth('7xl')
                    ->mutateRecordDataUsing(function (array $data, $record): array {
                        $user = $record->user;
                        if ($user) {
                            $data['user_name'] = $user->name;
                            $data['user_username'] = $user->username;
                            $data['user_email'] = $user->email;
                        }

                        return $data;
                    }),
                EditAction::make()
                    ->modal()
                    ->modalWidth('7xl')
                    ->mutateRecordDataUsing(function (array $data, $record): array {
                        $user = $record->user;
                        if ($user) {
                            $data['user_name'] = $user->name;
                            $data['user_username'] = $user->username;
                            $data['user_email'] = $user->email;
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
                            ]);

                            if (! empty($data['user_password'])) {
                                $updateData['password'] = Hash::make($data['user_password']);
                            }

                            $user->update($updateData);
                        }

                        unset($data['user_name'], $data['user_username'], $data['user_email'], $data['user_password']);

                        return $data;
                    }),
                DeleteAction::make()
                    ->modal()
                    ->modalWidth('7xl'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
