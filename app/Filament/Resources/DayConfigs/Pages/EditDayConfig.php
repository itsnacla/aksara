<?php

namespace App\Filament\Resources\DayConfigs\Pages;

use App\Filament\Resources\DayConfigs\DayConfigResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDayConfig extends EditRecord
{
    protected static string $resource = DayConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
