<?php

namespace App\Filament\Resources\StudentParents\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StudentParentInfoList
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
                TextEntry::make('hubungan')
                    ->label('Hubungan'),
                TextEntry::make('no_whatsapp')
                    ->label('No WhatsApp'),
            ]);
    }
}