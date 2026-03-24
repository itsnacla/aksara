<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Name'),
                TextEntry::make('username')
                    ->label('Username'),
                TextEntry::make('email')
                    ->label('Email Address'),
                TextEntry::make('role_user')
                    ->label('Role'),
                TextEntry::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
