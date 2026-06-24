<?php

namespace App\Filament\Resources\P5Project\Pages;

use App\Filament\Resources\P5Project\P5ProjectResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditP5Project extends EditRecord
{
    protected static string $resource = P5ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
