<?php

namespace App\Filament\Resources\StudentLeaves\Pages;

use App\Filament\Resources\StudentLeaves\StudentLeaveResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageStudentLeaves extends ManageRecords
{
    protected static string $resource = StudentLeaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
