<?php

namespace App\Filament\Resources\TimeSlots\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TimeSlotForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('levels')
                    ->label('Tingkatan (Level)')
                    ->relationship('levels', 'nama_tingkatan')
                    ->multiple()
                    ->preload()
                    ->placeholder('Pilih Tingkatan')
                    ->helperText('Pilih satu atau lebih tingkatan yang menggunakan jam ini'),

                TextInput::make('nama_jam')
                    ->label('Nama Jam')
                    ->required()
                    ->placeholder('Contoh: Jam Pertama, Istirahat, dll'),

                TimePicker::make('waktu_mulai')
                    ->label('Waktu Mulai')
                    ->required()
                    ->seconds(false),

                TimePicker::make('waktu_selesai')
                    ->label('Waktu Selesai')
                    ->required()
                    ->seconds(false),

                Toggle::make('is_istirahat')
                    ->label('Apakah Jam Istirahat?')
                    ->default(false),

                TextInput::make('urutan')
                    ->label('Urutan Tampilan')
                    ->numeric()
                    ->default(1)
                    ->required(),
            ]);
    }
}
