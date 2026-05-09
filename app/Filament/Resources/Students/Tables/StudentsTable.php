<?php

namespace App\Filament\Resources\Students\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_siswa')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nisn')
                    ->label('NISN')
                    ->copyable(),

                TextColumn::make('jenis_kelamin')
                    ->label('L/P'),

                TextColumn::make('created_at')
                    ->label('Terdaftar Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ]);
    }
}