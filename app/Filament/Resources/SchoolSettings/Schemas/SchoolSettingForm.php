<?php

namespace App\Filament\Resources\SchoolSettings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use App\Services\RegionService;

class SchoolSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Sekolah')
                    ->description('Informasi dasar dan branding institusi')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Sekolah')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('npsn')
                            ->label('NPSN')
                            ->maxLength(20)
                            ->placeholder('Nomor Pokok Sekolah Nasional'),
                        TextInput::make('motto')
                            ->label('Moto / Slogan Sekolah')
                            ->maxLength(255),
                        FileUpload::make('logo')
                            ->label('Logo Resmi Sekolah')
                            ->image()
                            ->disk('public')
                            ->directory('school')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Lokasi & Alamat')
                    ->description('Detail alamat fisik sekolah untuk keperluan administrasi')
                    ->schema([
                        Textarea::make('address')
                            ->label('Alamat Lengkap (Jalan)')
                            ->rows(3)
                            ->columnSpanFull(),
                        Select::make('province')
                            ->label('Provinsi')
                            ->options(RegionService::getProvinces())
                            ->searchable()
                            ->live()
                            ->dehydrateStateUsing(fn ($state) => RegionService::getProvinceName($state)),
                        Select::make('city')
                            ->label('Kabupaten/Kota')
                            ->options(fn (Get $get) => RegionService::getRegencies($get('province')))
                            ->searchable()
                            ->live()
                            ->dehydrateStateUsing(fn ($state, Get $get) => RegionService::getRegencyName($state, $get('province'))),
                        Select::make('district')
                            ->label('Kecamatan')
                            ->options(fn (Get $get) => RegionService::getDistricts($get('city')))
                            ->searchable()
                            ->live()
                            ->dehydrateStateUsing(fn ($state, Get $get) => RegionService::getDistrictName($state, $get('city'))),
                        Select::make('village')
                            ->label('Kelurahan/Desa')
                            ->options(fn (Get $get) => RegionService::getVillages($get('district')))
                            ->searchable()
                            ->dehydrateStateUsing(fn ($state, Get $get) => RegionService::getVillageName($state, $get('district'))),
                    ])
                    ->columns(1),

                Section::make('Kontak & Media')
                    ->description('Informasi kontak resmi sekolah')
                    ->schema([
                        TextInput::make('phone')
                            ->label('No. Telepon Sekolah')
                            ->tel(),
                        TextInput::make('email')
                            ->label('Email Resmi')
                            ->email(),
                        TextInput::make('website')
                            ->label('Website Sekolah')
                            ->url()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
