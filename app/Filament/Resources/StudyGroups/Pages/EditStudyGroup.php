<?php

namespace App\Filament\Resources\StudyGroups\Pages;

use App\Filament\Resources\StudyGroups\StudyGroupResource;
use App\Models\StudyGroup;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditStudyGroup extends EditRecord
{
    protected static string $resource = StudyGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function (Actions\DeleteAction $action, StudyGroup $record) {
                    $hasStudents = $record->students()->exists();
                    $hasSchedules = $record->schedules()->exists();

                    if ($hasStudents || $hasSchedules) {
                        $relatedItems = [];
                        if ($hasStudents) {
                            $relatedItems[] = 'Siswa';
                        }
                        if ($hasSchedules) {
                            $relatedItems[] = 'Jadwal';
                        }

                        Notification::make()
                            ->title('Tidak Dapat Menghapus Rombel')
                            ->danger()
                            ->body('Rombel ini masih memiliki data terkait: '.implode(', ', $relatedItems).'. Lepaskan atau hapus data terkait terlebih dahulu.')
                            ->persistent()
                            ->send();

                        $action->cancel();
                    }
                }),
        ];
    }
}
