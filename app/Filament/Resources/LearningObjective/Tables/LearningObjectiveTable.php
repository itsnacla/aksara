<?php

namespace App\Filament\Resources\LearningObjective\Tables;

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
                        if ($user && $user->hasRole('guru') && !$user->hasAnyRole(['super_admin', 'staff']) && $user->teacher) {
                            $teacherId = $user->teacher->id;
                            $managedLevelIds = \App\Models\StudyGroup::where('walikelas_id', $teacherId)->pluck('level_id')->toArray();
                            $query->where(function ($q) use ($teacherId, $managedLevelIds) {
                                $q->whereHas('schedules', fn ($sq) => $sq->where('teacher_id', $teacherId))
                                  ->orWhereHas('levels', fn ($lq) => $lq->whereIn('levels.id', $managedLevelIds));
                            });
                        }
                    })
                    ->label('Mata Pelajaran'),
                \Filament\Tables\Filters\SelectFilter::make('level_id')
                    ->relationship('level', 'nama_tingkatan', modifyQueryUsing: function ($query) {
                        $user = auth()->user();
                        if ($user && $user->hasRole('guru') && !$user->hasAnyRole(['super_admin', 'staff']) && $user->teacher) {
                            $teacherId = $user->teacher->id;
                            $managedLevelIds = \App\Models\StudyGroup::where('walikelas_id', $teacherId)->pluck('level_id')->toArray();
                            $query->where(function ($q) use ($teacherId, $managedLevelIds) {
                                $q->whereHas('subjects.schedules', fn ($sq) => $sq->where('teacher_id', $teacherId))
                                  ->orWhereIn('levels.id', $managedLevelIds);
                            });
                        }
                    })
                    ->label('Tingkatan'),
            ])
            ->actions([
                EditAction::make()->modalWidth('5xl'),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
