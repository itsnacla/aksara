<?php

namespace App\Filament\Resources\GraduateProfile\Pages;

use App\Filament\Resources\GraduateProfile\GraduateProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageGraduateProfiles extends ManageRecords
{
    protected static string $resource = GraduateProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
