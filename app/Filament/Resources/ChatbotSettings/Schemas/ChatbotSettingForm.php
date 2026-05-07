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
        return $schema
            ->components([
                Fieldset::make('Pengaturan Utama')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Aktifkan Chatbot AI')
                            ->helperText('Matikan untuk menonaktifkan chatbot sementara.')
                            ->columnSpanFull(),

                        Select::make('provider')
                            ->label('Provider AI Utama')
                            ->options([
                                'gemini' => '🟢 Google Gemini (Gratis)',
                                'openai' => '🔵 OpenAI (GPT-4o)',
                                'groq' => '🟠 Groq (Llama 3 — Gratis)',
                            ])
                            ->required()
                            ->native(false)
                            ->helperText('Provider yang digunakan untuk merespons chat.'),

                        TextInput::make('fallback_providers')
                            ->label('Fallback Providers')
                            ->placeholder('groq,openai')
                            ->helperText('Provider cadangan jika utama gagal (pisahkan dengan koma).'),
                    ])
                    ->columns(2),

                Fieldset::make('🟢 Google Gemini')
                    ->schema([
                        TextInput::make('gemini_api_key')
                            ->label('API Key')
                            ->password()
                            ->revealable()
                            ->placeholder('AIzaSy...'),

                        Select::make('gemini_model')
                            ->label('Model')
                            ->options([
                                'gemini-2.0-flash' => 'Gemini 2.0 Flash',
                                'gemini-2.0-flash-lite' => 'Gemini 2.0 Flash Lite',
                                'gemini-2.5-flash-preview-05-20' => 'Gemini 2.5 Flash (Preview)',
                                'gemini-2.5-pro-preview-05-06' => 'Gemini 2.5 Pro (Preview)',
                            ])
                            ->native(false)
                            ->default('gemini-2.0-flash'),
                    ])
                    ->columns(2),

                Fieldset::make('🔵 OpenAI')
                    ->schema([
                        TextInput::make('openai_api_key')
                            ->label('API Key')
                            ->password()
                            ->revealable()
                            ->placeholder('sk-...'),

                        Select::make('openai_model')
                            ->label('Model')
                            ->options([
                                'gpt-4o-mini' => 'GPT-4o Mini (Murah)',
                                'gpt-4o' => 'GPT-4o',
                                'gpt-4.1-mini' => 'GPT-4.1 Mini',
                                'gpt-4.1' => 'GPT-4.1',
                            ])
                            ->native(false)
                            ->default('gpt-4o-mini'),

                        TextInput::make('openai_base_url')
                            ->label('Base URL')
                            ->placeholder('https://api.openai.com/v1')
                            ->helperText('Ubah jika pakai proxy atau OpenRouter.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Fieldset::make('🟠 Groq')
                    ->schema([
                        TextInput::make('groq_api_key')
                            ->label('API Key')
                            ->password()
                            ->revealable()
                            ->placeholder('gsk_...'),

                        Select::make('groq_model')
                            ->label('Model')
                            ->options([
                                'llama-3.3-70b-versatile' => 'Llama 3.3 70B',
                                'llama-3.1-8b-instant' => 'Llama 3.1 8B (Cepat)',
                                'mixtral-8x7b-32768' => 'Mixtral 8x7B',
                                'gemma2-9b-it' => 'Gemma 2 9B',
                            ])
                            ->native(false)
                            ->default('llama-3.3-70b-versatile'),
                    ])
                    ->columns(2),
            ]);
    }
}
