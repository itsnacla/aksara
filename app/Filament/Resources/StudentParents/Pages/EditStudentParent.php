<?php

namespace App\Filament\Resources\StudentParents\Pages;

use App\Filament\Resources\StudentParents\StudentParentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStudentParent extends EditRecord
{
    protected static string $resource = StudentParentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
