<?php

namespace App\Filament\Resources\ChatbotSettings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

class ChatbotSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        // Daftar semua provider yang didukung laravel/ai untuk text generation
        $allProviders = [
            'gemini' => ['name' => 'Google Gemini'],
            'openai' => ['name' => 'OpenAI'],
            'anthropic' => ['name' => 'Anthropic (Claude)'],
            'groq' => ['name' => 'Groq'],
            'deepseek' => ['name' => 'DeepSeek'],
            'xai' => ['name' => 'xAI (Grok)'],
            'mistral' => ['name' => 'Mistral AI'],
            'openrouter' => ['name' => 'OpenRouter'],
            'ollama' => ['name' => 'Ollama (Local)'],
            'azure' => ['name' => 'Azure OpenAI'],
            'bedrock' => ['name' => 'AWS Bedrock'],
        ];

        $components = [
            Fieldset::make('Pengaturan Utama')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Aktifkan Chatbot AI')
                        ->columnSpanFull(),

                    Select::make('primary_provider')
                        ->label('Provider AI Utama')
                        ->options(collect($allProviders)->mapWithKeys(fn ($p, $k) => [$k => $p['name']])->toArray())
                        ->required()
                        ->native(false)
                        ->live(), // Membuat field ini reaktif

                    TextInput::make('fallback_providers')
                        ->label('Fallback Providers')
                        ->placeholder('groq,openai')
                        ->helperText('Pisahkan dengan koma.')
                        ->live(), // Membuat field ini reaktif
                ])
                ->columns(2),
        ];

        // Loop untuk membuat Fieldset dinamis per provider dengan logika visibilitas
        foreach ($allProviders as $key => $info) {
            $components[] = Fieldset::make($info['name'])
                ->schema([
                    TextInput::make("settings.{$key}.key")
                        ->label('API Key')
                        ->password()
                        ->revealable()
                        ->placeholder('Masukkan API Key...'),

                    TextInput::make("settings.{$key}.model")
                        ->label('Default Model')
                        ->placeholder(match ($key) {
                            'gemini' => 'gemini-2.0-flash',
                            'openai' => 'gpt-4o-mini',
                            'groq' => 'llama-3.3-70b-versatile',
                            'anthropic' => 'claude-3-5-sonnet-latest',
                            'deepseek' => 'deepseek-chat',
                            default => 'model-name'
                        }),

                    TextInput::make("settings.{$key}.url")
                        ->label('Custom Base URL')
                        ->placeholder('Optional')
                        ->columnSpanFull()
                        ->visible(in_array($key, ['openai', 'anthropic', 'gemini', 'groq', 'deepseek', 'xai', 'openrouter', 'ollama'])),
                ])
                ->columns(2)
                ->visible(fn ($get) => $get('primary_provider') === $key ||
                    str_contains((string) ($get('fallback_providers') ?? ''), $key)
                );
        }



        return $schema->components($components);
    }
}
