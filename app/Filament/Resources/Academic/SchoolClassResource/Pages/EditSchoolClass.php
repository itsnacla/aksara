<?php

namespace App\Filament\Resources\Academic\SchoolClassResource\Pages;

use App\Filament\Resources\Academic\SchoolClassResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSchoolClass extends EditRecord
{
    protected static string $resource = SchoolClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}