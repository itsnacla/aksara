<?php

namespace App\Filament\Resources\P5GroupResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class P5GroupTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Kelompok')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('level.nama_tingkatan')
                    ->label('Tingkat')
                    ->sortable(),
                TextColumn::make('project.fase')
                    ->label('Fase')
                    ->sortable(),
                TextColumn::make('teacher.user.name')
                    ->label('Koordinator')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('project.name')
                    ->label('Data Kokurikuler')
                    ->searchable()
                    ->wrap()
                    ->sortable(),
                TextColumn::make('students_count')
                    ->counts('students')
                    ->label('Anggota')
                    ->badge(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('level_id')
                    ->relationship('level', 'nama_tingkatan')
                    ->label('Tingkat'),
                \Filament\Tables\Filters\SelectFilter::make('teacher_id')
                    ->options(\App\Models\Teacher::with('user')->get()->pluck('user.name', 'id'))
                    ->label('Koordinator'),
                \Filament\Tables\Filters\SelectFilter::make('p5_project_id')
                    ->relationship('project', 'name')
                    ->label('Kegiatan'),
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
