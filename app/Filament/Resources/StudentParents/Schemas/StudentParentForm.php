<?php

namespace App\Filament\Resources\StudentParents\Schemas;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class StudentParentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Pilih Akun User (Wali)')
                    ->options(User::role('wali')->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                TextInput::make('nama_wali')
                    ->required()
                    ->maxLength(100),
                Select::make('hubungan')
                    ->options([
                        'ayah' => 'Ayah',
                        'ibu' => 'Ibu',
                        'wali' => 'Wali/Lainnya',
                    ])
                    ->required(),
                TextInput::make('no_whatsapp')
                    ->tel()
                    ->maxLength(20),
            ]);
    }
}
