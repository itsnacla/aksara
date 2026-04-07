<?php

namespace App\Filament\Resources\Teachers\Schemas;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TeacherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Pilih Akun User')
                    ->options(User::role('guru')->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                TextInput::make('nip')
                    ->label('NIP')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20),
                TextInput::make('nama_guru')
                    ->label('Nama Lengkap Guru')
                    ->required()
                    ->maxLength(100),
                TextInput::make('spesialisasi')
                    ->maxLength(50),
                TextInput::make('no_whatsapp')
                    ->label('No. WhatsApp')
                    ->tel()
                    ->maxLength(20),
                Toggle::make('is_walikelas')
                    ->label('Wali Kelas'),
                Toggle::make('is_kepalasekolah')
                    ->label('Kepala Sekolah'),
            ]);
    }
}
