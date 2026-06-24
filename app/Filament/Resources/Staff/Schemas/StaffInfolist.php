<?php

namespace App\Filament\Resources\Staff\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StaffInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('Nama Lengkap'),
                TextEntry::make('user.username')
                    ->label('Username'),
                TextEntry::make('user.email')
                    ->label('Email'),
                TextEntry::make('jabatan')
                    ->label('Jabatan'),
                TextEntry::make('no_whatsapp')
                    ->label('No WhatsApp')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
