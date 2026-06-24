<?php

namespace App\Filament\Resources\AcademicYears\Pages;

use App\Filament\Resources\AcademicYears\AcademicYearResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

use App\Filament\Resources\AcademicYears\Widgets\ActiveAcademicYearPrintDates;

class ListAcademicYears extends ListRecords
{
    protected static string $resource = AcademicYearResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ActiveAcademicYearPrintDates::class,
        ];
    }
}
