<?php

namespace App\Filament\Resources\P5Group\Pages;

use App\Filament\Resources\P5Group\P5GroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditP5Group extends EditRecord
{
    protected static string $resource = P5GroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
