<?php

namespace App\Filament\Resources\Staff\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StaffForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->label('User ID')
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('nama_staff')
                    ->label('Nama Lengkap Staff')
                    ->required(),
                TextInput::make('jabatan')
                    ->label('Jabatan')
                    ->required(),
                TextInput::make('no_whatsapp')
                    ->label('No WhatsApp'), 
            ]);
    }
}
