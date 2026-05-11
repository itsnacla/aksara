<?php

namespace App\Filament\Resources\SchoolSettings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class SchoolSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Sekolah')
                    ->required()
                    ->maxLength(255),
                TextInput::make('motto')
                    ->label('Moto Sekolah')
                    ->maxLength(255),
                FileUpload::make('logo')
                    ->label('Logo Sekolah')
                    ->image()
                    ->disk('public')
                    ->directory('school'),
                Textarea::make('address')
                    ->label('Alamat Sekolah')
                    ->rows(3),
                TextInput::make('phone')
                    ->label('Telepon')
                    ->tel(),
                TextInput::make('email')
                    ->label('Email Sekolah')
                    ->email(),
                TextInput::make('website')
                    ->label('Website')
                    ->url(),
            ]);
    }
}
