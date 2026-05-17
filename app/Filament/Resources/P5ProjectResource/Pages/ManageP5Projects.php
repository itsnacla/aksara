<?php

namespace App\Filament\Resources\P5ProjectResource\Pages;

use App\Filament\Resources\P5ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageP5Projects extends ManageRecords
{
    protected static string $resource = P5ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modalWidth('lg'),
        ];
    }
}
