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
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2) // Set global columns to 2 for the whole dialog
            ->components([
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
                    ])
                    ->columnSpan(1),

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

                        // Sub-form for creating a new parent account
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
                    ])
                    ->columnSpan(1),

                Section::make('Identitas Dasar Siswa')
                    ->description('Informasi identitas resmi dan penempatan rombel')
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

                        TextInput::make('nisn')
                            ->label('NISN')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(10),
                        TextInput::make('nis')
                            ->label('NIS (Lokal)')
                            ->maxLength(20),
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
                        Toggle::make('lives_with_parent')
                            ->label('Tinggal Bersama Orang Tua')
                            ->default(true)
                            ->live()
                            ->columnSpanFull(),
                        Textarea::make('address')
                            ->label('Alamat Tinggal Siswa')
                            ->rows(2)
                            ->columnSpanFull()
                            ->visible(fn (Get $get) => !$get('lives_with_parent'))
                            ->required(fn (Get $get) => !$get('lives_with_parent')),
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
                    ])
                    ->columnSpan(1),

                Section::make('Informasi Orang Tua Kandung')
                    ->description('Data Ayah dan Ibu Kandung')
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
                            ->required()
                            ->rows(2)
                            ->columnSpanFull(),
                        Select::make('parent.province')
                            ->label('Provinsi')
                            ->options(fn () => RegionService::getProvinces())
                            ->required()
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
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($set) {
                                $set('parent.district', null);
                                $set('parent.village', null);
                            }),
                        Select::make('parent.district')
                            ->label('Kecamatan')
                            ->options(fn (Get $get) => RegionService::getDistricts($get('parent.city')))
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($set) {
                                $set('parent.village', null);
                            }),
                        Select::make('parent.village')
                            ->label('Desa')
                            ->options(fn (Get $get) => RegionService::getVillages($get('parent.district'), $get('parent.city')))
                            ->required()
                            ->searchable(),
                    ])
                    ->columnSpan(1),

                Section::make('Informasi Wali Murid (Opsional)')
                    ->description('Hanya jika tidak tinggal bersama orang tua kandung')
                    ->schema([
                        TextInput::make('parent.guardian_name')
                            ->label('Nama Wali')
                            ->maxLength(255),
                        TextInput::make('parent.guardian_occupation')
                            ->label('Pekerjaan Wali')
                            ->maxLength(255),
                        Textarea::make('parent.guardian_address')
                            ->label('Alamat Wali')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull() // This section stays at the bottom, taking full width
                    ->collapsed(),
            ]);
    }
}
