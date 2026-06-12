<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Models\User;
use App\Models\AcademicYear;
use App\Models\StudyGroup;
use App\Models\StudentParent;
use App\Services\RegionService;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Data Siswa Lengkap')
                    ->tabs([
                        static::getAccountTab(),
                        static::getPersonalTab(),
                        static::getFamilyTab(),
                        static::getHealthTab(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected static function getAccountTab(): Tab
    {
        return Tab::make('Akun & Rombel')
            ->icon('heroicon-o-user-circle')
            ->schema([
                Grid::make(2)->schema([
                    // Kolom 1: Akun Siswa & Koneksi Orang Tua (Sesuai Layout Asli)
                    Grid::make(1)->schema([
                        Section::make('Informasi Akun Siswa')
                            ->description('Data login dan foto profil siswa')
                            ->schema([
                                TextInput::make('user_name')
                                    ->label('Nama Lengkap Siswa')
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
                                TextInput::make('user_password')
                                    ->label('Password')
                                    ->password()
                                    ->revealable()
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn (?string $state) => filled($state))
                                    ->maxLength(255),
                                Toggle::make('user_is_active')
                                    ->label('Akun Aktif')
                                    ->default(true),
                                FileUpload::make('user_photo')
                                    ->label('Foto Siswa')
                                    ->image()
                                    ->avatar()
                                    ->disk('public')
                                    ->directory('student-photos')
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Koneksi Akun Orang Tua')
                            ->description('Hubungkan dengan akun portal wali')
                            ->schema([
                                Toggle::make('create_new_parent')
                                    ->label('Buat Akun Login Orang Tua Baru?')
                                    ->default(fn (string $operation) => $operation === 'create')
                                    ->visible(fn (string $operation) => $operation === 'create')
                                    ->live()
                                    ->columnSpanFull(),

                                Select::make('parent_id')
                                    ->label('Pilih Akun Wali Murid yang Sudah Ada')
                                    ->options(fn () => StudentParent::with('user')->get()->pluck('user.name', 'id'))
                                    ->required(fn (Get $get, string $operation) => $operation === 'edit' || !$get('create_new_parent'))
                                    ->visible(fn (Get $get, string $operation) => $operation === 'edit' || !$get('create_new_parent'))
                                    ->searchable()
                                    ->columnSpanFull(),

                                TextInput::make('parent_name')
                                    ->label('Nama Lengkap Akun')
                                    ->required(fn (Get $get) => $get('create_new_parent'))
                                    ->visible(fn (Get $get) => $get('create_new_parent'))
                                    ->maxLength(255),
                                TextInput::make('parent_username')
                                    ->label('Username Login')
                                    ->required(fn (Get $get) => $get('create_new_parent'))
                                    ->visible(fn (Get $get) => $get('create_new_parent'))
                                    ->unique('users', 'username')
                                    ->maxLength(255),
                                TextInput::make('parent_email')
                                    ->label('Email Akun')
                                    ->email()
                                    ->required(fn (Get $get) => $get('create_new_parent'))
                                    ->visible(fn (Get $get) => $get('create_new_parent'))
                                    ->unique('users', 'email')
                                    ->maxLength(255),
                                TextInput::make('parent_password')
                                    ->label('Password Akun')
                                    ->password()
                                    ->revealable()
                                    ->required(fn (Get $get) => $get('create_new_parent'))
                                    ->visible(fn (Get $get) => $get('create_new_parent'))
                                    ->maxLength(255),
                                TextInput::make('parent_whatsapp')
                                    ->label('No. WhatsApp (Aktif)')
                                    ->tel()
                                    ->visible(fn (Get $get) => $get('create_new_parent'))
                                    ->maxLength(20),
                            ]),
                    ])->columnSpan(1),

                    // Kolom 2: Penempatan Rombel & Status (Sesuai Layout Asli)
                    Section::make('Penempatan & Status')
                        ->description('Informasi pendaftaran dan rombel')
                        ->schema([
                            Select::make('studyGroups')
                                ->label('Penempatan Rombel / Kelas')
                                ->relationship('studyGroups', 'nama_rombel')
                                ->multiple()
                                ->options(function () {
                                    $activeYear = AcademicYear::where('is_active', true)->first();
                                    if (!$activeYear) return [];
                                    return StudyGroup::where('academic_year_id', $activeYear->id)
                                        ->with('level')
                                        ->get()
                                        ->groupBy(fn($sg) => $sg->level->nama_tingkatan ?? 'Lainnya')
                                        ->map(fn($group) => $group->pluck('nama_rombel', 'id'))
                                        ->toArray();
                                })
                                ->required()
                                ->searchable()
                                ->columnSpanFull(),

                            Select::make('status')
                                ->label('Status Siswa')
                                ->options([
                                    'aktif' => 'Aktif',
                                    'lulus' => 'Lulus',
                                    'mutasi' => 'Mutasi',
                                    'keluar' => 'Keluar/Berhenti',
                                ])
                                ->required()
                                ->default('aktif'),
                            TextInput::make('previous_school')
                                ->label('Asal Sekolah Sebelumnya')
                                ->maxLength(255),
                        ])->columnSpan(1),
                ]),
            ]);
    }

    protected static function getPersonalTab(): Tab
    {
        return Tab::make('Identitas Pribadi')
            ->icon('heroicon-o-identification')
            ->schema([
                Grid::make(3)->schema([
                    TextInput::make('nisn')
                        ->label('NISN')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(10),
                    TextInput::make('nis')
                        ->label('NIS (Lokal)')
                        ->maxLength(20),
                    TextInput::make('nik')
                        ->label('NIK (Sesuai KK)')
                        ->maxLength(20),
                    TextInput::make('no_kk')
                        ->label('Nomor KK')
                        ->maxLength(20),
                    TextInput::make('no_akta_lahir')
                        ->label('No. Akta Lahir')
                        ->maxLength(50),
                    TextInput::make('pob')
                        ->label('Tempat Lahir')
                        ->maxLength(255),
                    DatePicker::make('dob')
                        ->label('Tanggal Lahir'),
                    Select::make('gender')
                        ->label('Jenis Kelamin')
                        ->options([
                            'L' => 'Laki-laki',
                            'P' => 'Perempuan',
                        ])
                        ->required(),
                    Select::make('religion')
                        ->label('Agama')
                        ->options([
                            'Islam' => 'Islam',
                            'Kristen' => 'Kristen',
                            'Katolik' => 'Katolik',
                            'Hindu' => 'Hindu',
                            'Buddha' => 'Buddha',
                            'Khonghucu' => 'Khonghucu',
                        ])
                        ->searchable(),
                    TextInput::make('phone')
                        ->label('No. Telepon Siswa')
                        ->tel(),
                ]),
                Section::make('Alamat Domisili Siswa')
                    ->schema([
                        Toggle::make('lives_with_parent')
                            ->label('Tinggal Bersama Orang Tua')
                            ->default(true)
                            ->live()
                            ->columnSpanFull(),
                        Textarea::make('address')
                            ->label('Alamat Lengkap')
                            ->rows(2)
                            ->columnSpanFull()
                            ->visible(fn (Get $get) => !$get('lives_with_parent'))
                            ->required(fn (Get $get) => !$get('lives_with_parent')),
                        Grid::make(4)->schema([
                            Select::make('province')
                                ->label('Provinsi')
                                ->options(fn () => RegionService::getProvinces())
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(function ($set) {
                                    $set('city', null);
                                    $set('district', null);
                                    $set('village', null);
                                })
                                ->visible(fn (Get $get) => !$get('lives_with_parent'))
                                ->required(fn (Get $get) => !$get('lives_with_parent')),
                            Select::make('city')
                                ->label('Kota')
                                ->options(fn (Get $get) => RegionService::getRegencies($get('province')))
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(function ($set) {
                                    $set('district', null);
                                    $set('village', null);
                                })
                                ->visible(fn (Get $get) => !$get('lives_with_parent'))
                                ->required(fn (Get $get) => !$get('lives_with_parent')),
                            Select::make('district')
                                ->label('Kecamatan')
                                ->options(fn (Get $get) => RegionService::getDistricts($get('city')))
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(fn ($set) => $set('village', null))
                                ->visible(fn (Get $get) => !$get('lives_with_parent'))
                                ->required(fn (Get $get) => !$get('lives_with_parent')),
                            Select::make('village')
                                ->label('Desa')
                                ->options(fn (Get $get) => RegionService::getVillages($get('district'), $get('city')))
                                ->searchable()
                                ->visible(fn (Get $get) => !$get('lives_with_parent'))
                                ->required(fn (Get $get) => !$get('lives_with_parent')),
                        ]),
                    ]),
            ]);
    }

    protected static function getFamilyTab(): Tab
    {
        return Tab::make('Keluarga & Wali')
            ->icon('heroicon-o-users')
            ->schema([
                Grid::make(2)->schema([
                    Section::make('Informasi Orang Tua Kandung')
                        ->description('Data Ayah dan Ibu Kandung')
                        ->visible(fn (Get $get, string $operation) => $operation === 'edit' || $get('create_new_parent'))
                        ->schema([
                            TextInput::make('parent.father_name')
                                ->label('Nama Ayah')
                                ->maxLength(255),
                            TextInput::make('parent.father_occupation')
                                ->label('Pekerjaan Ayah')
                                ->maxLength(255),
                            TextInput::make('parent.mother_name')
                                ->label('Nama Ibu')
                                ->maxLength(255),
                            TextInput::make('parent.mother_occupation')
                                ->label('Pekerjaan Ibu')
                                ->maxLength(255),
                            Textarea::make('parent.address')
                                ->label('Alamat Orang Tua')
                                ->required(fn (Get $get, string $operation) => $operation === 'edit' || $get('create_new_parent'))
                                ->rows(2)
                                ->columnSpanFull(),
                            Grid::make(2)->schema([
                                Select::make('parent.province')
                                    ->label('Provinsi')
                                    ->options(fn () => RegionService::getProvinces())
                                    ->required(fn (Get $get, string $operation) => $operation === 'edit' || $get('create_new_parent'))
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function ($set) {
                                        $set('parent.city', null);
                                        $set('parent.district', null);
                                        $set('parent.village', null);
                                    }),
                                Select::make('parent.city')
                                    ->label('Kota')
                                    ->options(fn (Get $get) => RegionService::getRegencies($get('parent.province')))
                                    ->required(fn (Get $get, string $operation) => $operation === 'edit' || $get('create_new_parent'))
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function ($set) {
                                        $set('parent.district', null);
                                        $set('parent.village', null);
                                    }),
                                Select::make('parent.district')
                                    ->label('Kecamatan')
                                    ->options(fn (Get $get) => RegionService::getDistricts($get('parent.city')))
                                    ->required(fn (Get $get, string $operation) => $operation === 'edit' || $get('create_new_parent'))
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function ($set) {
                                        $set('parent.village', null);
                                    }),
                                Select::make('parent.village')
                                    ->label('Desa')
                                    ->options(fn (Get $get) => RegionService::getVillages($get('parent.district'), $get('parent.city')))
                                    ->required(fn (Get $get, string $operation) => $operation === 'edit' || $get('create_new_parent'))
                                    ->searchable(),
                            ]),
                        ])->columnSpan(1),

                    Section::make('Detail Buku Induk (Master Data)')
                        ->description('Data tambahan sesuai standar Buku Induk Nasional')
                        ->schema([
                            TextInput::make('ayah_nama')->label('Nama Lengkap Ayah (Sesuai Ijazah)'),
                            TextInput::make('ayah_nik')->label('NIK Ayah'),
                            TextInput::make('ayah_pendidikan')->label('Pendidikan Terakhir'),
                            TextInput::make('ayah_pekerjaan')->label('Pekerjaan'),
                            TextInput::make('ayah_penghasilan')->label('Penghasilan Bulanan'),
                            
                            TextInput::make('ibu_nama')->label('Nama Lengkap Ibu (Sesuai Ijazah)'),
                            TextInput::make('ibu_nik')->label('NIK Ibu'),
                            TextInput::make('ibu_pendidikan')->label('Pendidikan Terakhir'),
                            TextInput::make('ibu_pekerjaan')->label('Pekerjaan'),
                            TextInput::make('ibu_penghasilan')->label('Penghasilan Bulanan'),
                        ])->columnSpan(1),
                ]),

                Section::make('Informasi Wali Murid (Opsional)')
                    ->description('Hanya jika tidak tinggal bersama orang tua kandung')
                    ->visible(fn (Get $get, string $operation) => $operation === 'edit' || $get('create_new_parent'))
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('parent.guardian_name')
                                ->label('Nama Wali')
                                ->maxLength(255),
                            TextInput::make('parent.guardian_occupation')
                                ->label('Pekerjaan Wali')
                                ->maxLength(255),
                            TextInput::make('wali_hubungan')
                                ->label('Hubungan dengan Siswa'),
                        ]),
                        Textarea::make('parent.guardian_address')
                            ->label('Alamat Wali')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }

    protected static function getHealthTab(): Tab
    {
        return Tab::make('Kesehatan & Lainnya')
            ->icon('heroicon-o-heart')
            ->schema([
                Grid::make(3)->schema([
                    TextInput::make('anak_ke')->label('Anak Ke')->numeric(),
                    TextInput::make('jumlah_saudara')->label('Jumlah Saudara')->numeric(),
                    TextInput::make('tinggi_badan')->label('Tinggi Badan (cm)')->numeric()->suffix('cm'),
                    TextInput::make('berat_badan')->label('Berat Badan (kg)')->numeric()->suffix('kg'),
                    Select::make('golongan_darah')->label('Golongan Darah')->options(['A' => 'A', 'B' => 'B', 'AB' => 'AB', 'O' => 'O']),
                ]),
            ]);
    }
}
