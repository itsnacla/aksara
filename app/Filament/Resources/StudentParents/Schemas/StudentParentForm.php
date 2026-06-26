<?php

namespace App\Filament\Resources\StudentParents\Schemas;

use App\Services\RegionService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class StudentParentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Akun Akses Orang Tua')
                    ->description('Informasi login untuk wali murid di aplikasi')
                    ->schema([
                        TextInput::make('user_name')
                            ->label('Nama Lengkap Pemilik Akun')
                            ->placeholder('Contoh: Budi Santoso')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('user_username')
                            ->label('Username')
                            ->required()
                            ->maxLength(255)
                            ->unique(table: 'users', column: 'username', ignorable: fn ($record) => $record?->user),
                        TextInput::make('user_email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(table: 'users', column: 'email', ignorable: fn ($record) => $record?->user),
                        TextInput::make('no_whatsapp')
                            ->label('No. WhatsApp (Aktif)')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('08123456789'),
                        TextInput::make('user_password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state) => filled($state))
                            ->maxLength(255)
                            ->helperText(fn (string $operation): string => $operation === 'edit' ? 'Kosongkan jika tidak ingin mengubah password' : ''),
                    ])
                    ->columns(1),

                Section::make('Data Orang Tua Kandung')
                    ->description('Data administratif Ayah dan Ibu Kandung')
                    ->schema([
                        Fieldset::make('Informasi Ayah')
                            ->schema([
                                TextInput::make('father_name')
                                    ->label('Nama Lengkap Ayah')
                                    ->maxLength(255),
                                TextInput::make('father_occupation')
                                    ->label('Pekerjaan Ayah')
                                    ->maxLength(255),
                            ]),

                        Fieldset::make('Informasi Ibu')
                            ->schema([
                                TextInput::make('mother_name')
                                    ->label('Nama Lengkap Ibu')
                                    ->maxLength(255),
                                TextInput::make('mother_occupation')
                                    ->label('Pekerjaan Ibu')
                                    ->maxLength(255),
                            ]),
                    ])
                    ->columns(1),

                Section::make('Alamat Domisili Orang Tua')
                    ->schema([
                        Textarea::make('address')
                            ->label('Alamat Lengkap (Jalan)')
                            ->rows(2)
                            ->columnSpanFull(),

                        Select::make('province')
                            ->label('Provinsi')
                            ->options(fn () => RegionService::getProvinces())
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($set) {
                                $set('city', null);
                                $set('district', null);
                                $set('village', null);
                            }),
                        Select::make('city')
                            ->label('Kabupaten/Kota')
                            ->options(fn (Get $get) => RegionService::getRegencies($get('province')))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($set) {
                                $set('district', null);
                                $set('village', null);
                            }),
                        Select::make('district')
                            ->label('Kecamatan')
                            ->options(fn (Get $get) => RegionService::getDistricts($get('city')))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn ($set) => $set('village', null)),
                        Select::make('village')
                            ->label('Kelurahan/Desa')
                            ->options(fn (Get $get) => RegionService::getVillages($get('district'), $get('city')))
                            ->searchable(),
                    ])
                    ->columns(1),

                Section::make('Data Wali (Jika Ada)')
                    ->description('Diisi hanya jika siswa tidak tinggal bersama orang tua kandung')
                    ->schema([
                        TextInput::make('guardian_name')
                            ->label('Nama Lengkap Wali')
                            ->maxLength(255),
                        TextInput::make('guardian_occupation')
                            ->label('Pekerjaan Wali')
                            ->maxLength(255),
                        Textarea::make('guardian_address')
                            ->label('Alamat Lengkap Wali')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->collapsed(),
            ]);
    }
}
