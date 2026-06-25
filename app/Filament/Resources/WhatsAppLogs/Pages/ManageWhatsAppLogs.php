<?php

namespace App\Filament\Resources\WhatsAppLogs\Pages;

use App\Filament\Resources\WhatsAppLogs\WhatsAppLogResource;
use Filament\Resources\Pages\ManageRecords;

class ManageWhatsAppLogs extends ManageRecords
{
    protected static string $resource = WhatsAppLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
