<?php

namespace App\Filament\Resources\Subjects\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_mapel')
                    ->label('Nama Mata Pelajaran')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('kode_mapel')
                    ->label('Kode Mata Pelajaran (Otomatis)')
                    ->disabled()
                    ->dehydrated()
                    ->placeholder('Akan diisi otomatis...')
                    ->maxLength(30),
                \Filament\Forms\Components\Toggle::make('is_umum')
                    ->label('Mapel Umum (Wali Kelas)')
                    ->default(true)
                    ->helperText('Jika aktif, guru pengajar akan otomatis Wali Kelas.'),
                TextInput::make('total_jp')
                    ->label('Total Jam Pelajaran (JP)')
                    ->helperText('Jumlah total jam pelajaran untuk mata pelajaran ini dalam satu minggu.')
                    ->numeric()
                    ->default(2)
                    ->required(),
                \Filament\Forms\Components\Select::make('levels')
                    ->label('Tingkatan (Level)')
                    ->relationship('levels', 'nama_tingkatan')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->helperText('Pilih satu atau lebih tingkatan kelas yang menggunakan mata pelajaran ini.'),
                TextInput::make('kkm')
                    ->label('KKM')
                    ->numeric()
                    ->default(75)
                    ->required(),
                \Filament\Forms\Components\Toggle::make('is_one_day_finish')
                    ->label('Selesai Dalam 1 Hari')
                    ->helperText('Jika aktif, seluruh JP akan dipaksakan selesai dalam satu hari (seperti PJOK).')
                    ->default(false),
                \Filament\Forms\Components\Select::make('scheduling_priority')
                    ->label('Prioritas Penjadwalan')
                    ->options([
                        1 => 'Normal (Biasa)',
                        2 => 'Sedang (Distribusi Lebih Awal)',
                        3 => 'Tinggi (Distribusi Pertama)',
                    ])
                    ->default(1)
                    ->helperText('Prioritas tinggi akan dijadwalkan lebih awal untuk menghindari bentrok.'),
            ]);
    }
}
