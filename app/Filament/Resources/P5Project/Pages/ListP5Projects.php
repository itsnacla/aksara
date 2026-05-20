<?php

namespace App\Filament\Resources\P5Project\Pages;

use App\Filament\Resources\P5Project\P5ProjectResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListP5Projects extends ListRecords
{
    protected static string $resource = P5ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
