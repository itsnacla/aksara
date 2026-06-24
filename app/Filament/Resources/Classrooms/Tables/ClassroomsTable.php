<?php

namespace App\Filament\Resources\Classrooms\Tables;

use App\Models\Classroom;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class ClassroomsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_ruangan')
                    ->label('Nama Ruangan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make()->modal(),
                EditAction::make()->modal(),
                DeleteAction::make()
                    ->modal()
                    ->before(function (DeleteAction $action, Classroom $record) {
                        // Check if classroom has study groups with students
                        $studyGroupsWithStudents = $record->studyGroups()
                            ->whereHas('students')
                            ->exists();

                        if ($studyGroupsWithStudents) {
                            Notification::make()
                                ->title('Tidak Dapat Menghapus Ruangan')
                                ->danger()
                                ->body('Ruangan ini masih memiliki kelas dengan siswa. Hapus kelas terlebih dahulu.')
                                ->persistent()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
