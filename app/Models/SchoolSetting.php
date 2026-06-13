<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string|null $school_level
 * @property string|null $npsn
 * @property string|null $nis_nss_nds
 * @property string|null $logo
 * @property string|null $logo_pemda
 * @property string|null $address
 * @property string|null $village
 * @property string|null $district
 * @property string|null $city
 * @property string|null $province
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $website
 * @property string|null $motto
 * @property bool $is_wa_enabled
 * @property string|null $wa_gateway_url
 * @property string|null $wa_gateway_token
 * @property string|null $wa_gateway_provider
 * @property string|null $wa_gateway_phone_param
 * @property string|null $wa_gateway_message_param
 * @property bool $wa_notify_attendance
 * @property bool $wa_notify_announcement
 */
#[Fillable([
    'id', // Diperlukan agar updateOrCreate di seeder/migration tidak error
    'name',
    'school_level',
    'npsn',
    'nis_nss_nds',
    'logo',
    'logo_pemda',
    'address',
    'village',
    'district',
    'city',
    'province',
    'phone',
    'email',
    'website',
    'motto',
    'is_wa_enabled',
    'wa_gateway_url',
    'wa_gateway_token',
    'wa_gateway_provider',
    'wa_gateway_phone_param',
    'wa_gateway_message_param',
    'wa_notify_attendance',
    'wa_notify_announcement',
])]
class SchoolSetting extends Model
{
    protected $casts = [
        'is_wa_enabled' => 'boolean',
        'wa_notify_attendance' => 'boolean',
        'wa_notify_announcement' => 'boolean',
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
            'motto' => 'Membangun Karakter, Meraih Prestasi.',
        ]);
    }
}
