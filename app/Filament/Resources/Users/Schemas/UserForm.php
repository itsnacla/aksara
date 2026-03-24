<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required(),
                TextInput::make('username')
                    ->label('Username')
                    ->required(),
                TextInput::make('email')
                    ->label('Email Address')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->required(),
                Select::make('role_user')
                    ->label('Role')
                    ->options([
                        'admin' => 'Admin',
                        'staff' => 'Staff',
                        'guru' => 'Guru',
                        'siswa' => 'Siswa',
                        'wali' => 'Wali'
                    ])
                    ->required(),
            ]);
    }
}
