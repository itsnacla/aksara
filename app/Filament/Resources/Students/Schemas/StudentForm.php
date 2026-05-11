<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('Data Akun User')
                    ->schema([
                        TextInput::make('user_name')
                            ->label('Nama Lengkap')
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
                            ->maxLength(255)
                            ->helperText(fn (string $operation): string => $operation === 'edit' ? 'Kosongkan jika tidak ingin mengubah password' : ''),
                        Toggle::make('user_is_active')
                            ->label('Akun Aktif')
                            ->default(true)
                            ->helperText('Jika dinonaktifkan, user ini tidak bisa login.'),
                        FileUpload::make('user_photo')
                            ->label('Foto Siswa')
                            ->image()
                            ->avatar()
                            ->disk('public')
                            ->directory('student-photos')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Fieldset::make('Data Siswa')
                    ->schema([
                        TextInput::make('nisn')
                            ->label('NISN')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(10),
                        
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

                        TextInput::make('pob')
                            ->label('Tempat Lahir')
                            ->maxLength(255),
                            
                        \Filament\Forms\Components\DatePicker::make('dob')
                            ->label('Tanggal Lahir'),

                        Select::make('gender')
                            ->label('Jenis Kelamin')
                            ->options([
                                'L' => 'Laki-laki',
                                'P' => 'Perempuan',
                            ])
                            ->required(),

                        TextInput::make('religion')
                            ->label('Agama')
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('No. Telepon Siswa')
                            ->tel()
                            ->maxLength(255),

                        \Filament\Forms\Components\Textarea::make('address')
                            ->label('Alamat Lengkap')
                            ->columnSpanFull(),

                        Select::make('studyGroups')
                            ->label('Rombel')
                            ->relationship('studyGroups', 'nama_rombel')
                            ->multiple()
                            ->options(fn () => \App\Models\StudyGroup::whereHas('academicYear', fn ($q) => $q->where('is_active', true))->get()->mapWithKeys(fn ($rombel) => [
                                $rombel->id => "{$rombel->nama_rombel} ({$rombel->academicYear->tahun_ajaran})"
                            ]))
                            ->required()
                            ->searchable(),
                        Toggle::make('create_new_parent')
                            ->label('Buat Akun Orang Tua Baru?')
                            ->default(fn (string $operation) => $operation === 'create')
                            ->visible(fn (string $operation) => $operation === 'create')
                            ->live()
                            ->columnSpanFull(),
                        Select::make('parent_id')
                            ->label('Orang Tua/Wali')
                            ->options(fn () => \App\Models\StudentParent::with('user')->get()->pluck('user.name', 'id'))
                            ->required(fn (Get $get, string $operation) => $operation === 'edit' || !$get('create_new_parent'))
                            ->visible(fn (Get $get, string $operation) => $operation === 'edit' || !$get('create_new_parent'))
                            ->searchable(),
                    ])
                    ->columns(2),

                Fieldset::make('Data Orang Tua Baru')
                    ->schema([
                        TextInput::make('parent_name')
                            ->label('Nama Lengkap Orang Tua')
                            ->required(fn (Get $get) => $get('create_new_parent'))
                            ->maxLength(255),
                        TextInput::make('parent_username')
                            ->label('Username Orang Tua')
                            ->required(fn (Get $get) => $get('create_new_parent'))
                            ->unique('users', 'username')
                            ->maxLength(255),
                        TextInput::make('parent_email')
                            ->label('Email Orang Tua')
                            ->email()
                            ->required(fn (Get $get) => $get('create_new_parent'))
                            ->unique('users', 'email')
                            ->maxLength(255),
                        TextInput::make('parent_password')
                            ->label('Password Orang Tua')
                            ->password()
                            ->revealable()
                            ->required(fn (Get $get) => $get('create_new_parent'))
                            ->maxLength(255),
                        Select::make('parent_relation')
                            ->label('Hubungan')
                            ->options([
                                'ayah' => 'Ayah',
                                'ibu' => 'Ibu',
                                'wali' => 'Wali/Lainnya',
                            ])
                            ->required(fn (Get $get) => $get('create_new_parent')),
                        TextInput::make('parent_whatsapp')
                            ->label('No. WhatsApp Orang Tua')
                            ->tel()
                            ->maxLength(20),
                    ])
                    ->visible(fn (Get $get, string $operation) => $operation === 'create' && $get('create_new_parent'))
                    ->columns(2),
            ]);
    }
}
