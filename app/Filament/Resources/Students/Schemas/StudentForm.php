<?php

namespace App\Filament\Resources\Students\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        // Pastikan ada return $schema->components([...])
        return $schema
            ->components([
                // 1. Input Nama
                TextInput::make('nama_siswa')
                    ->label('Nama Lengkap')
                    ->required(), // Ini biar nggak bisa dikosongin lagi!

                // 2. Input NISN
                TextInput::make('nisn')
                    ->label('NISN')
                    ->numeric()
                    ->required(),

                // 3. Pilih Gender
                Select::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                    ])
                    ->required(),
            ]);
    }
}