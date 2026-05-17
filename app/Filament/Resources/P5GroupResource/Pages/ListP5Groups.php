<?php

namespace App\Filament\Resources\P5GroupResource\Pages;

use App\Filament\Resources\P5GroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListP5Groups extends ListRecords
{
    protected static string $resource = P5GroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
