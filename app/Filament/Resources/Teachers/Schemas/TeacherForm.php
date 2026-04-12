<?php

namespace App\Filament\Resources\Teachers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

class TeacherForm
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
                            ->unique(table: 'users', column: 'username', ignoreRecord: true),
                        TextInput::make('user_email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(table: 'users', column: 'email', ignoreRecord: true),
                        TextInput::make('user_password')
                            ->label('Password')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state) => filled($state))
                            ->maxLength(255)
                            ->helperText(fn (string $operation): string => $operation === 'edit' ? 'Kosongkan jika tidak ingin mengubah password' : ''),
                    ])
                    ->columns(2),

                Fieldset::make('Data Guru')
                    ->schema([
                        TextInput::make('nip')
                            ->label('NIP')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20),

                        TextInput::make('spesialisasi')
                            ->maxLength(50),
                        TextInput::make('no_whatsapp')
                            ->label('No. WhatsApp')
                            ->tel()
                            ->maxLength(20),
                        Toggle::make('is_walikelas')
                            ->label('Wali Kelas'),
                        Toggle::make('is_kepalasekolah')
                            ->label('Kepala Sekolah'),
                    ])
                    ->columns(2),
            ]);
    }
}
