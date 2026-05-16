<?php

namespace App\Filament\Resources\Extracurriculars\Schemas;

use App\Models\Staff;
use App\Models\Teacher;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ExtracurricularForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_ekskul')
                    ->label('Nama Ekstrakurikuler')
                    ->required()
                    ->maxLength(50)
                    ->placeholder('Contoh: Pramuka, Futsal, PMR'),
                Select::make('kategori')
                    ->label('Kategori')
                    ->options([
                        'wajib' => 'Wajib',
                        'pilihan' => 'Pilihan',
                    ])
                    ->default('pilihan')
                    ->required(),
                TextInput::make('nilai_minimum')
                    ->label('Nilai Minimum (Standar)')
                    ->placeholder('Contoh: B atau 75')
                    ->maxLength(20),
                Select::make('pembina')
                    ->label('Pembina / Pelatih')
                    ->options(function () {
                        $teachers = Teacher::with('user')->get()->mapWithKeys(function ($teacher) {
                            $name = $teacher->user->name ?? 'N/A';
                            return [$name => "[Guru] " . $name];
                        });

                        $staff = Staff::with('user')->get()->mapWithKeys(function ($staffMember) {
                            $name = $staffMember->user->name ?? 'N/A';
                            return [$name => "[Staf] " . $name];
                        });

                        return $teachers->merge($staff)->toArray();
                    })
                    ->searchable()
                    ->placeholder('Pilih Guru atau Staf sebagai Pembina'),
                Textarea::make('deskripsi')
                    ->label('Deskripsi Kegiatan')
                    ->required()
                    ->rows(3)
                    ->placeholder('Jelaskan kegiatan rutin dan tujuan ekskul ini...'),
            ]);
    }
}
