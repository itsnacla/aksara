<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotSetting extends Model
{
    protected $fillable = [
        'provider',
        'fallback_providers',
        'gemini_api_key',
        'gemini_model',
        'openai_api_key',
        'openai_model',
        'openai_base_url',
        'groq_api_key',
        'groq_model',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the singleton settings instance (there should only be one row).
     */
    public static function current(): static
    {
        return static::firstOrCreate([], [
            'provider' => config('chatbot.provider', 'gemini'),
            'gemini_api_key' => config('chatbot.providers.gemini.api_key', ''),
            'gemini_model' => config('chatbot.providers.gemini.model', 'gemini-2.0-flash'),
            'openai_api_key' => config('chatbot.providers.openai.api_key', ''),
            'openai_model' => config('chatbot.providers.openai.model', 'gpt-4o-mini'),
            'openai_base_url' => config('chatbot.providers.openai.base_url', 'https://api.openai.com/v1'),
            'groq_api_key' => config('chatbot.providers.groq.api_key', ''),
            'groq_model' => config('chatbot.providers.groq.model', 'llama-3.3-70b-versatile'),
        ]);
    }

    /**
     * Get the API key for a given provider name.
     */
    public function getApiKeyFor(string $provider): ?string
    {
        return match ($provider) {
            'gemini' => $this->gemini_api_key,
            'openai' => $this->openai_api_key,
            'groq' => $this->groq_api_key,
            default => null,
        };
    }

    /**
     * Get the model name for a given provider name.
     */
    public function getModelFor(string $provider): string
    {
        return match ($provider) {
            'gemini' => $this->gemini_model ?: 'gemini-2.0-flash',
            'openai' => $this->openai_model ?: 'gpt-4o-mini',
            'groq' => $this->groq_model ?: 'llama-3.3-70b-versatile',
            default => '',
        };
    }

    /**
     * Get fallback providers as an array.
     */
    public function getFallbackProvidersArray(): array
    {
        return array_filter(explode(',', $this->fallback_providers ?? ''));
    }
}
