<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('Data Akun User')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('username')
                            ->label('Username')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state) => filled($state))
                            ->maxLength(255),
                        Select::make('selected_role')
                            ->label('Role')
                            ->options([
                                'super_admin' => 'Super Admin',
                                'staff' => 'Staff',
                                'guru' => 'Guru',
                                'siswa' => 'Siswa',
                                'wali' => 'Orang Tua / Wali',
                            ])
                            ->required()
                            ->live(),
                    ])
                    ->columns(2),

                Fieldset::make('Data Guru')
                    ->schema([
                        TextInput::make('teacher_nip')
                            ->label('NIP')
                            ->required(fn (Get $get): bool => $get('selected_role') === 'guru')
                            ->maxLength(20),
                        TextInput::make('teacher_spesialisasi')
                            ->label('Spesialisasi')
                            ->maxLength(50),
                        TextInput::make('teacher_no_whatsapp')
                            ->label('No. WhatsApp')
                            ->tel()
                            ->maxLength(20),
                        Toggle::make('teacher_is_walikelas')
                            ->label('Wali Kelas'),
                        Toggle::make('teacher_is_kepalasekolah')
                            ->label('Kepala Sekolah'),
                    ])
                    ->columns(2)
                    ->visible(fn (Get $get): bool => $get('selected_role') === 'guru'),

                Fieldset::make('Data Staff')
                    ->schema([
                        TextInput::make('staff_jabatan')
                            ->label('Jabatan')
                            ->required(fn (Get $get): bool => $get('selected_role') === 'staff')
                            ->maxLength(50),
                        TextInput::make('staff_no_whatsapp')
                            ->label('No. WhatsApp')
                            ->tel()
                            ->maxLength(20),
                    ])
                    ->columns(2)
                    ->visible(fn (Get $get): bool => $get('selected_role') === 'staff'),

                Fieldset::make('Data Siswa')
                    ->schema([
                        TextInput::make('student_nisn')
                            ->label('NISN')
                            ->required(fn (Get $get): bool => $get('selected_role') === 'siswa')
                            ->maxLength(10),
                        Select::make('student_classroom_id')
                            ->label('Kelas')
                            ->options(fn () => \App\Models\Classroom::pluck('nama_kelas', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(fn (Get $get): bool => $get('selected_role') === 'siswa'),
                        Select::make('student_parent_id')
                            ->label('Orang Tua / Wali')
                            ->options(fn () => \App\Models\StudentParent::with('user')->get()->pluck('user.name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(fn (Get $get): bool => $get('selected_role') === 'siswa'),
                    ])
                    ->columns(2)
                    ->visible(fn (Get $get): bool => $get('selected_role') === 'siswa'),

                Fieldset::make('Data Orang Tua / Wali')
                    ->schema([
                        Select::make('parent_hubungan')
                            ->label('Hubungan')
                            ->options([
                                'ayah' => 'Ayah',
                                'ibu' => 'Ibu',
                                'wali' => 'Wali/Lainnya',
                            ])
                            ->required(fn (Get $get): bool => $get('selected_role') === 'wali'),
                        TextInput::make('parent_no_whatsapp')
                            ->label('No. WhatsApp')
                            ->tel()
                            ->maxLength(20),
                    ])
                    ->columns(2)
                    ->visible(fn (Get $get): bool => $get('selected_role') === 'wali'),
            ]);
    }
}
