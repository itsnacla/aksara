<?php

namespace App\Filament\Resources\Extracurriculars\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExtracurricularsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_ekskul')
                    ->label('Nama Ekstrakurikuler')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('kategori')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'wajib' => 'danger',
                        'pilihan' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucwords($state)),
                TextColumn::make('students_count')
                    ->label('Anggota')
                    ->counts('students')
                    ->badge()
                    ->color('info'),
                TextColumn::make('coordinator.nama_lengkap')
                    ->label('Koordinator')
                    ->placeholder('Belum ditentukan')
                    ->searchable(['name']),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('kategori')
                    ->options([
                        'wajib' => 'Wajib',
                        'pilihan' => 'Pilihan',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
