<?php

namespace App\Filament\Resources\Teachers\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TeacherInfoList
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nama_lengkap')
                    ->label('Nama Lengkap'),
                TextEntry::make('user.username')
                    ->label('Username'),
                TextEntry::make('user.email')
                    ->label('Email'),
                TextEntry::make('nip')
                    ->label('NIP'),
                TextEntry::make('subjects.nama_mapel')
                    ->label('Mata Pelajaran yang Diampu')
                    ->badge(),
                IconEntry::make('is_walikelas')
                    ->label('Wali Kelas')
                    ->boolean(),
                IconEntry::make('is_kepalasekolah')
                    ->label('Kepala Sekolah')
                    ->boolean(),
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
