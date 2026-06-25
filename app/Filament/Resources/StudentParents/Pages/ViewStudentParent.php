<?php

namespace App\Filament\Resources\StudentParents\Pages;

use App\Filament\Resources\StudentParents\StudentParentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStudentParent extends ViewRecord
{
    protected static string $resource = StudentParentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
