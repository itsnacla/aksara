<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $primary_provider
 * @property string|null $fallback_providers
 * @property array|null $settings
 * @property bool $is_active
 */
#[Fillable([
    'id',
    'primary_provider',
    'fallback_providers',
    'settings',
    'is_active',
])]
class ChatbotSetting extends Model
{
    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        // Decrypt keys when retrieving from database
        static::retrieved(function ($model) {
            if (is_array($model->settings)) {
                $settings = $model->settings;
                foreach ($settings as $provider => $config) {
                    if (isset($config['key']) && ! empty($config['key'])) {
                        try {
                            $settings[$provider]['key'] = decrypt($config['key']);
                        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                            // If it fails, keep it as is (it was already plain text)
                        }
                    }
                }
                $model->attributes['settings'] = json_encode($settings);
            }
        });

        // Encrypt keys before saving to database
        static::saving(function ($model) {
            if (is_array($model->settings)) {
                $settings = $model->settings;
                foreach ($settings as $provider => $config) {
                    if (isset($config['key']) && ! empty($config['key'])) {
                        try {
                            // Verify if it's already encrypted
                            decrypt($config['key']);
                        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                            // If it fails, it means it is a new plain-text key, so encrypt it!
                            $settings[$provider]['key'] = encrypt($config['key']);
                        }
                    }
                }
                $model->attributes['settings'] = json_encode($settings);
            }
        });
    }

    /**
     * Get the current chatbot settings (singleton).
     */
    public static function current(): self
    {
        return self::first() ?: self::create([
            'primary_provider' => 'google',
            'is_active' => true,
            'settings' => [],
        ]);
    }

    /**
     * Helper to get nested setting value
     */
    public function getProviderSetting(string $provider, string $key, mixed $default = null): mixed
    {
        return $this->settings[$provider][$key] ?? $default;
    }

    public function getProviderAttribute(): string
    {
        return $this->primary_provider;
    }

    public function getFallbackProvidersArray(): array
    {
        if (empty($this->fallback_providers)) {
            return [];
        }
        if (is_array($this->fallback_providers)) {
            return $this->fallback_providers;
        }

        return array_filter(array_map('trim', explode(',', $this->fallback_providers)));
    }

    public function getApiKeyFor(string $provider): ?string
    {
        return $this->settings[$provider]['key'] ?? null;
    }

    public function getModelFor(string $provider): ?string
    {
        $model = $this->settings[$provider]['model'] ?? null;
        if (empty($model)) {
            return match ($provider) {
                'gemini' => 'gemini-2.0-flash',
                'openai' => 'gpt-4o-mini',
                'groq' => 'llama3-8b-8192',
                default => null,
            };
        }

        return $model;
    }

    public function getOpenaiBaseUrlAttribute(): ?string
    {
        return $this->settings['openai']['url'] ?? null;
    }
}
