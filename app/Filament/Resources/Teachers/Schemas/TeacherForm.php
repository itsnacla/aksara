<?php

namespace App\Filament\Resources\Teachers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use App\Models\Teacher;
use Closure;

class TeacherForm
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
                    ])
                    ->columns(2),

                Fieldset::make('Data Guru')
                    ->schema([
                        TextInput::make('gelar_depan')
                            ->label('Gelar Depan')
                            ->placeholder('Contoh: Drs., Dra., Ir.')
                            ->maxLength(20),

                        TextInput::make('nip')
                            ->label('NIP')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20),

                        TextInput::make('gelar_belakang')
                            ->label('Gelar Belakang')
                            ->placeholder('Contoh: S.Pd., M.Pd.')
                            ->maxLength(50),
                        
                        Select::make('status')
                            ->label('Status Guru')
                            ->options([
                                'aktif' => 'Aktif',
                                'mutasi' => 'Mutasi',
                                'pensiun' => 'Pensiun',
                                'berhenti' => 'Berhenti Kerja',
                            ])
                            ->required()
                            ->default('aktif'),

                        TextInput::make('kode_guru')
                            ->label('Kode Guru (Otomatis)')
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('Akan diisi otomatis...')
                            ->maxLength(30),

                        TextInput::make('no_whatsapp')
                            ->label('No. WhatsApp')
                            ->tel()
                            ->maxLength(20),
                        Toggle::make('is_walikelas')
                            ->label('Wali Kelas'),
                        Toggle::make('is_kepalasekolah')
                            ->label('Kepala Sekolah')
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $component) {
                                if ($state) {
                                    $existing = Teacher::where('is_kepalasekolah', true)
                                        ->where('id', '!=', $component->getRecord()?->id)
                                        ->exists();
                                    
                                    if ($existing) {
                                        // Optional: warning or handle automatically. 
                                        // Better to use a validation rule.
                                    }
                                }
                            })
                            ->rules([
                                function ($get, $component) {
                                    return function (string $attribute, $value, Closure $fail) use ($component) {
                                        if ($value === true) {
                                            $exists = Teacher::where('is_kepalasekolah', true)
                                                ->where('id', '!=', $component->getRecord()?->id)
                                                ->exists();
                                            
                                            if ($exists) {
                                                $fail('Sudah ada guru lain yang menjabat sebagai Kepala Sekolah!');
                                            }
                                        }
                                    };
                                },
                            ]),
                        Select::make('subjects')
                            ->label('Mata Pelajaran yang Diampu')
                            ->relationship('subjects', 'nama_mapel')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->helperText('Pilih mata pelajaran yang diajar oleh guru ini.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
