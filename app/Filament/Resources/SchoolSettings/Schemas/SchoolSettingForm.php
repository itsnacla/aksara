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
use App\Services\SchoolRegionService;
use App\Services\KemendikbudService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

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
                        Select::make('school_level')
                            ->label('Tingkat Sekolah')
                            ->options([
                                'SD' => 'SD',
                                'SMP' => 'SMP',
                                'SMA' => 'SMA',
                                'SMK' => 'SMK',
                            ])
                            ->placeholder('Pilih Tingkat Sekolah'),
                        TextInput::make('npsn')
                            ->label('NPSN')
                            ->maxLength(20)
                            ->placeholder('Nomor Pokok Sekolah Nasional')
                            ->suffixAction(
                                Action::make('fetchFromKemendikbud')
                                    ->icon('heroicon-m-arrow-path')
                                    ->tooltip('Tarik data dari Kemendikbud')
                                    ->action(function ($state, $set) {
                                        if (!$state) {
                                            Notification::make()
                                                ->title('NPSN Kosong')
                                                ->warning()
                                                ->send();
                                            return;
                                        }

                                        $data = KemendikbudService::fetchByNpsn($state);

                                        if (!$data['success']) {
                                            Notification::make()
                                                ->title('Gagal Menarik Data')
                                                ->body($data['message'])
                                                ->danger()
                                                ->send();
                                            return;
                                        }

                                        $set('name', $data['name']);
                                        if (isset($data['school_level']) && in_array($data['school_level'], ['SD', 'SMP', 'SMA', 'SMK'])) {
                                            $set('school_level', $data['school_level']);
                                        }
                                        $set('address', $data['address']);
                                        $set('email', $data['email']);
                                        $set('website', $data['website']);
                                        
                                        // Mapping Region Names to IDs with careful state setting
                                        $provinceId = SchoolRegionService::findProvinceIdByName($data['province']) ?? $data['province'];
                                        $set('province', $provinceId);
                                        
                                        $cityId = SchoolRegionService::findRegencyIdByName($provinceId, $data['city']) ?? $data['city'];
                                        $set('city', $cityId);
                                        
                                        $districtId = SchoolRegionService::findDistrictIdByName($cityId, $data['district']) ?? $data['district'];
                                        $set('district', $districtId);
                                        
                                        $villageId = SchoolRegionService::findVillageIdByName($districtId, $data['village']) ?? $data['village'];
                                        $set('village', $villageId);

                                        Notification::make()
                                            ->title('Data Ditemukan')
                                            ->body('Identitas sekolah berhasil diperbarui dari referensi Kemendikbud.')
                                            ->success()
                                            ->send();
                                    })
                            ),
                        TextInput::make('nis_nss_nds')
                            ->label('NIS / NSS / NDS')
                            ->maxLength(20)
                            ->placeholder('Masukkan NIS, NSS, atau NDS Sekolah'),
                        FileUpload::make('logo')
                            ->label('Logo Sekolah')
                            ->image()
                            ->directory('school-settings')
                            ->maxSize(1024)
                            ->columnSpan('full')
                            ->helperText('Rekomendasi ukuran: 200x200px (Max 1MB). Akan digunakan pada kop surat dan laporan.'),
                            
                        FileUpload::make('kop_surat')
                            ->label('Kop Surat Khusus (Opsional)')
                            ->image()
                            ->directory('school-settings/kop')
                            ->maxSize(2048)
                            ->columnSpan('full'),
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
                            ->options(fn () => SchoolRegionService::getProvinces())
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($set) => $set('city', null)->set('district', null)->set('village', null)),
                        Select::make('city')
                            ->label('Kabupaten/Kota')
                            ->options(fn (callable $get) => SchoolRegionService::getRegencies($get('province')))
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($set) => $set('district', null)->set('village', null)),
                        Select::make('district')
                            ->label('Kecamatan')
                            ->options(fn (callable $get) => SchoolRegionService::getDistricts($get('city')))
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($set) => $set('village', null)),
                        Select::make('village')
                            ->label('Kelurahan/Desa')
                            ->options(fn (callable $get) => SchoolRegionService::getVillages($get('district')))
                            ->searchable()
                            ->required(),
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
