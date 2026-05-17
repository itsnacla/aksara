<?php

namespace App\Filament\Resources\SubjectReportGroupResource\Pages;

use App\Filament\Resources\SubjectReportGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSubjectReportGroups extends ManageRecords
{
    protected static string $resource = SubjectReportGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
