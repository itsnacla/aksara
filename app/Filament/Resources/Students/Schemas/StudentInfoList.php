<?php

namespace App\Filament\Resources\Students\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StudentInfoList
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun')
                    ->schema([
                        ImageEntry::make('user.photo')
                            ->label('Foto Profil')
                            ->circular(),
                        TextEntry::make('user.name')
                            ->label('Nama Lengkap'),
                        TextEntry::make('user.username')
                            ->label('Username'),
                        TextEntry::make('user.email')
                            ->label('Email'),
                    ])->columns(2),

                Section::make('Identitas Siswa')
                    ->schema([
                        TextEntry::make('nisn')
                            ->label('NISN'),
                        TextEntry::make('gender')
                            ->label('Jenis Kelamin')
                            ->formatStateUsing(fn ($state) => $state === 'L' ? 'Laki-laki' : 'Perempuan'),
                        TextEntry::make('pob')
                            ->label('Tempat Lahir'),
                        TextEntry::make('dob')
                            ->label('Tanggal Lahir')
                            ->date('d F Y'),
                        TextEntry::make('religion')
                            ->label('Agama'),
                        TextEntry::make('phone')
                            ->label('No. Telepon'),
                        TextEntry::make('address')
                            ->label('Alamat Lengkap')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Akademik & Keluarga')
                    ->schema([
                        TextEntry::make('studyGroup.nama_rombel')
                            ->label('Rombel Current')
                            ->badge(),
                        TextEntry::make('studyGroup.academicYear.tahun_ajaran')
                            ->label('Tahun Ajaran'),
                        TextEntry::make('parent.user.name')
                            ->label('Orang Tua/Wali'),
                    ])->columns(2),
            ]);
    }
}