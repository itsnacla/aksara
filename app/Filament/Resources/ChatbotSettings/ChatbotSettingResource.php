<?php

namespace App\Filament\Resources\ChatbotSettings;

use App\Filament\Resources\ChatbotSettings\Pages\ManageChatbotSetting;
use App\Filament\Resources\ChatbotSettings\Schemas\ChatbotSettingForm;
use App\Models\ChatbotSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ChatbotSettingResource extends Resource
{
    protected static ?string $model = ChatbotSetting::class;

    protected static ?string $recordTitleAttribute = 'primary_provider';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cpu-chip';

    protected static UnitEnum|string|null $navigationGroup = 'Sistem & Konfigurasi';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Chatbot AI';

    protected static ?string $modelLabel = 'Pengaturan Chatbot';

    protected static ?string $pluralModelLabel = 'Pengaturan Chatbot';

    public static function form(Schema $schema): Schema
    {
        return ChatbotSettingForm::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageChatbotSetting::route('/'),
        ];
    }
}
