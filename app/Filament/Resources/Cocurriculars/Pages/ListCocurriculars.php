<?php

namespace App\Filament\Resources\Cocurriculars\Pages;

use App\Filament\Resources\Cocurriculars\CocurricularResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCocurriculars extends ListRecords
{
    protected static string $resource = CocurricularResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modalWidth('lg'),
        ];
    }
}
