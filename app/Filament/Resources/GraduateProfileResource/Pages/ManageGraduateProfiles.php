<?php

namespace App\Filament\Resources\GraduateProfileResource\Pages;

use App\Filament\Resources\GraduateProfileResource;
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
