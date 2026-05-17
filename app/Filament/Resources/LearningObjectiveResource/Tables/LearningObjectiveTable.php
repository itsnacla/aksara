<?php

namespace App\Filament\Resources\LearningObjectiveResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LearningObjectiveTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('subject.nama_mapel')
                    ->label('Mata Pelajaran')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('level.nama_tingkatan')
                    ->label('Tingkatan')
                    ->sortable(),
                    
                TextColumn::make('description')
                    ->label('Deskripsi TP')
                    ->wrap()
                    ->searchable(),
                    
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('subject_id')
                    ->relationship('subject', 'nama_mapel', modifyQueryUsing: function ($query) {
                        $user = auth()->user();
                        if ($user && !$user->hasAnyRole(['super_admin', 'staff']) && $user->teacher) {
                            $query->whereIn('subjects.id', $user->teacher->subjects()->pluck('subjects.id')->toArray());
                        }
                    })
                    ->label('Mata Pelajaran'),
                \Filament\Tables\Filters\SelectFilter::make('level_id')
                    ->relationship('level', 'nama_tingkatan')
                    ->label('Tingkatan'),
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
