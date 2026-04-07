<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Akun User Siswa')
                    ->options(User::role('siswa')->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                TextInput::make('nisn')
                    ->label('NISN')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(10),
                TextInput::make('nama_siswa')
                    ->required()
                    ->maxLength(100),
                Select::make('classroom_id')
                    ->label('Kelas')
                    ->relationship('classroom', 'nama_kelas')
                    ->required()
                    ->searchable(),
                Select::make('parent_id')
                    ->label('Orang Tua/Wali')
                    ->relationship('parent', 'nama_wali') // Hubungkan ke relasi 'parent' di model Student
                    ->required()
                    ->searchable(),
                TextInput::make('qr_code')
                    ->label('QR Code ID')
                    ->maxLength(100)
                    ->disabled()
                    ->placeholder('Akan di-generate otomatis'),
            ]);
    }
}
