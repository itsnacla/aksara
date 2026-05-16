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
}
