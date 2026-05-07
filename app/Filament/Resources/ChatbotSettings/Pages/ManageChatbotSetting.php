<?php

namespace App\Filament\Resources\ChatbotSettings\Pages;

use App\Filament\Resources\ChatbotSettings\ChatbotSettingResource;
use App\Models\ChatbotSetting;
use Filament\Resources\Pages\EditRecord;

class ManageChatbotSetting extends EditRecord
{
    protected static string $resource = ChatbotSettingResource::class;

    protected static ?string $title = 'Pengaturan Chatbot AI';

    /**
     * Singleton pattern: always edit the single settings record.
     * Creates a default record if none exists.
     */
    public function mount(int|string $record = null): void
    {
        $settings = ChatbotSetting::current();
        parent::mount($settings->id);
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
