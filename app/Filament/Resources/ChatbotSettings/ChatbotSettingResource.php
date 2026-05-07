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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;

    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Chatbot AI';

    protected static ?string $modelLabel = 'Pengaturan Chatbot';

    protected static ?string $pluralModelLabel = 'Pengaturan Chatbot';

    protected static ?int $navigationSort = 99;

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
