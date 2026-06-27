<?php

namespace App\Filament\Resources\ChatbotRequestLogs\Pages;

use App\Filament\Resources\ChatbotRequestLogs\ChatbotRequestLogResource;
use Filament\Resources\Pages\ManageRecords;

class ManageChatbotRequestLogs extends ManageRecords
{
    protected static string $resource = ChatbotRequestLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
