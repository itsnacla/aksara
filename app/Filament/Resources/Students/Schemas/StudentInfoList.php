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
                        TextEntry::make('nis')
                            ->label('NIS'),
                        TextEntry::make('previous_school')
                            ->label('Pendidikan Sebelumnya'),
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
                            ->label('Alamat Lengkap Siswa')
                            ->formatStateUsing(function ($state, $record) {
                                if ($record->lives_with_parent && $record->parent) {
                                    $p = $record->parent;
                                    $full = "{$p->address}, Desa {$p->village}, Kec. {$p->district}, {$p->city}, {$p->province}";
                                    return $full . " (Ikut Orang Tua)";
                                }
                                return "{$state}, Desa {$record->village}, Kec. {$record->district}, {$record->city}, {$record->province}";
                            })
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Data Orang Tua Kandung')
                    ->schema([
                        TextEntry::make('parent.father_name')
                            ->label('Nama Ayah'),
                        TextEntry::make('parent.father_occupation')
                            ->label('Pekerjaan Ayah'),
                        TextEntry::make('parent.mother_name')
                            ->label('Nama Ibu'),
                        TextEntry::make('parent.mother_occupation')
                            ->label('Pekerjaan Ibu'),
                        TextEntry::make('parent.address')
                            ->label('Alamat Orang Tua')
                            ->columnSpanFull(),
                        TextEntry::make('parent.village')
                            ->label('Desa/Kel'),
                        TextEntry::make('parent.district')
                            ->label('Kecamatan'),
                        TextEntry::make('parent.city')
                            ->label('Kab/Kota'),
                        TextEntry::make('parent.province')
                            ->label('Provinsi'),
                    ])->columns(2),

                Section::make('Data Wali (Opsional)')
                    ->schema([
                        TextEntry::make('parent.guardian_name')
                            ->label('Nama Wali'),
                        TextEntry::make('parent.guardian_occupation')
                            ->label('Pekerjaan Wali'),
                        TextEntry::make('parent.guardian_address')
                            ->label('Alamat Wali')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Akun Sistem & Rombel')
                    ->schema([
                        TextEntry::make('studyGroups.nama_rombel')
                            ->label('Rombel Aktif')
                            ->badge(),
                        TextEntry::make('parent.user.name')
                            ->label('Tersambung Akun Login'),
                    ])->columns(2),
            ]);
    }
}