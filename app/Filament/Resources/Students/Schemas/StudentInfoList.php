<?php

namespace App\Filament\Resources\Students\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StudentInfoList
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('Nama Akun User'),
                TextEntry::make('user.username')
                    ->label('Username'),
                TextEntry::make('user.email')
                    ->label('Email'),
                TextEntry::make('nama_siswa')
                    ->label('Nama Lengkap Siswa'),
                TextEntry::make('nisn')
                    ->label('NISN'),
                TextEntry::make('classroom.nama_kelas')
                    ->label('Kelas'),
                TextEntry::make('parent.nama_wali')
                    ->label('Orang Tua/Wali'),
                TextEntry::make('qr_code')
                    ->label('QR Code ID')
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