<?php

namespace App\Filament\Resources\P5Theme\Pages;

use App\Filament\Resources\P5Theme\P5ThemeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageP5Themes extends ManageRecords
{
    protected static string $resource = P5ThemeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
