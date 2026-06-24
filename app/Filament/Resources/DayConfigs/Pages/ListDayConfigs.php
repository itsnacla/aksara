<?php

namespace App\Filament\Resources\DayConfigs\Pages;

use App\Filament\Resources\DayConfigs\DayConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDayConfigs extends ListRecords
{
    protected static string $resource = DayConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modal(),
        ];
    }
}
