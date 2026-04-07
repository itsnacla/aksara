<?php

namespace App\Filament\Resources\StudentParents\Pages;

use App\Filament\Resources\StudentParents\StudentParentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStudentParents extends ListRecords
{
    protected static string $resource = StudentParentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
