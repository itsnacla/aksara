<?php

namespace App\Filament\Resources\SubjectReportMapping\Pages;

use App\Filament\Resources\SubjectReportMapping\SubjectReportMappingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSubjectReportMappings extends ManageRecords
{
    protected static string $resource = SubjectReportMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
