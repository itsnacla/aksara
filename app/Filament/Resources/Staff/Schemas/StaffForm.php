<?php

namespace App\Filament\Resources\Staff\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

class StaffForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('Data Akun User')
                    ->schema([
                        TextInput::make('user_name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('user_username')
                            ->label('Username')
                            ->required()
                            ->maxLength(255)
                            ->unique(table: 'users', column: 'username', ignorable: fn ($record) => $record?->user),
                        TextInput::make('user_email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(table: 'users', column: 'email', ignorable: fn ($record) => $record?->user),
                        TextInput::make('user_password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state) => filled($state))
                            ->maxLength(255)
                            ->helperText(fn (string $operation): string => $operation === 'edit' ? 'Kosongkan jika tidak ingin mengubah password' : ''),
                        \Filament\Forms\Components\Toggle::make('user_is_active')
                            ->label('Akun Aktif')
                            ->default(true)
                            ->helperText('Jika dinonaktifkan, staf ini tidak bisa login.'),
                    ])
                    ->columns(2),

                Fieldset::make('Data Staff')
                    ->schema([

                        TextInput::make('jabatan')
                            ->label('Jabatan')
                            ->required(),
                        \Filament\Forms\Components\Select::make('status')
                            ->label('Status Kepegawaian')
                            ->options([
                                'aktif' => 'Aktif',
                                'pensiun' => 'Pensiun',
                                'berhenti' => 'Berhenti Kerja',
                            ])
                            ->required()
                            ->default('aktif'),
                        TextInput::make('no_whatsapp')
                            ->label('No WhatsApp')
                            ->tel()
                            ->maxLength(20),
                    ])
                    ->columns(2),
            ]);
    }
}
