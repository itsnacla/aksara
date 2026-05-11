<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    protected $fillable = [
        'name',
        'logo',
        'address',
        'phone',
        'email',
        'website',
        'motto',
        'wa_gateway_token',
        'wa_gateway_provider',
        'is_wa_enabled',
        'wa_gateway_url',
        'wa_gateway_phone_param',
        'wa_gateway_message_param',
    ];

    protected $casts = [
        'is_wa_enabled' => 'boolean',
    ];

    /**
     * Ensure website always has https prefix.
     */
    public function getWebsiteAttribute($value)
    {
        if (!$value) return null;
        if (!str_starts_with($value, 'http')) {
            return 'https://' . $value;
        }
        return $value;
    }

    /**
     * Get the current school settings (singleton).
     */
    public static function current(): self
    {
        return self::first() ?: self::create([
            'name' => 'Aksara Academic System',
            'motto' => 'Digital Education Excellence',
        ]);
    }
}
