<?php

namespace App\Filament\Resources\Extracurriculars\Schemas;

use App\Models\Staff;
use App\Models\Teacher;
use App\Models\User;
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
                Select::make('coordinator_user_id')
                    ->label('Koordinator / Pembina')
                    ->options(function () {
                        $teacherUserIds = Teacher::pluck('user_id')->filter();
                        $staffUserIds = Staff::pluck('user_id')->filter();
                        $userIds = $teacherUserIds->concat($staffUserIds)->unique();

                        return User::whereIn('id', $userIds)
                            ->get()
                            ->mapWithKeys(function ($user) use ($teacherUserIds) {
                                $prefix = $teacherUserIds->contains($user->id) ? '[Guru]' : '[Staf]';
                                return [$user->id => "{$prefix} {$user->name}"];
                            })
                            ->toArray();
                    })
                    ->searchable()
                    ->placeholder('Pilih Guru atau Staf sebagai Koordinator'),
                Textarea::make('deskripsi')
                    ->label('Deskripsi Kegiatan')
                    ->required()
                    ->rows(3)
                    ->placeholder('Jelaskan kegiatan rutin dan tujuan ekskul ini...'),
            ]);
    }
}
