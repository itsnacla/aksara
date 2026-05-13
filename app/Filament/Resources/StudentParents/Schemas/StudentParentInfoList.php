<?php

namespace App\Filament\Resources\StudentParents\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StudentParentInfoList
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun Login')
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Nama Lengkap Akun'),
                        TextEntry::make('user.username')
                            ->label('Username'),
                        TextEntry::make('user.email')
                            ->label('Email'),
                        TextEntry::make('no_whatsapp')
                            ->label('No WhatsApp'),
                    ])->columns(2),

                Section::make('Informasi Administratif Orang Tua')
                    ->schema([
                        TextEntry::make('father_name')
                            ->label('Nama Ayah'),
                        TextEntry::make('father_occupation')
                            ->label('Pekerjaan Ayah'),
                        TextEntry::make('mother_name')
                            ->label('Nama Ibu'),
                        TextEntry::make('mother_occupation')
                            ->label('Pekerjaan Ibu'),
                        TextEntry::make('address')
                            ->label('Alamat Lengkap')
                            ->columnSpanFull(),
                        TextEntry::make('village')
                            ->label('Kelurahan/Desa'),
                        TextEntry::make('district')
                            ->label('Kecamatan'),
                        TextEntry::make('city')
                            ->label('Kabupaten/Kota'),
                        TextEntry::make('province')
                            ->label('Provinsi'),
                    ])->columns(2),

                Section::make('Informasi Wali (Opsional)')
                    ->schema([
                        TextEntry::make('guardian_name')
                            ->label('Nama Wali'),
                        TextEntry::make('guardian_occupation')
                            ->label('Pekerjaan Wali'),
                        TextEntry::make('guardian_address')
                            ->label('Alamat Wali')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}