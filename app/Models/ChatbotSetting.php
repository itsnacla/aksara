<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
    ];

    /**
     * Accessor/Mutator for settings with per-key encryption.
     *
     * GET: Decodes JSON from DB, decrypts each provider API key.
     * SET: Encrypts each provider API key, then encodes to JSON for DB.
     *
     * This replaces the old boot() events which directly manipulated
     * $model->attributes['settings'], conflicting with the 'array' cast
     * and causing SQLSTATE[22P02] errors on PostgreSQL jsonb columns.
     */
    protected function settings(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value) {
                $settings = is_string($value) ? json_decode($value, true) : $value;

                if (! is_array($settings)) {
                    return $settings ?? [];
                }

                // Decrypt each provider's API key on read
                foreach ($settings as $provider => $config) {
                    if (isset($config['key']) && ! empty($config['key'])) {
                        try {
                            $settings[$provider]['key'] = decrypt($config['key']);
                        } catch (DecryptException $e) {
                            // Already plaintext or invalid — keep as-is
                        }
                    }
                }

                return $settings;
            },
            set: function (mixed $value) {
                if (! is_array($value)) {
                    return $value;
                }

                $settings = $value;

                // Encrypt each provider's API key on write
                foreach ($settings as $provider => $config) {
                    if (isset($config['key']) && ! empty($config['key'])) {
                        try {
                            // Test if already encrypted
                            decrypt($config['key']);
                            // If decrypt succeeds, it's already encrypted — keep as-is
                        } catch (DecryptException $e) {
                            // Plaintext key — encrypt it
                            $settings[$provider]['key'] = encrypt($config['key']);
                        }
                    }
                }

                return json_encode($settings);
            }
        );
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
