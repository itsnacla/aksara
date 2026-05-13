<?php

namespace App\Services;

use App\Models\SchoolSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WAService
{
    /**
     * Send a WhatsApp message using the configured gateway.
     */
    public static function sendMessage(string $phone, string $message): bool
    {
        $settings = SchoolSetting::current();

        if (!$settings->is_wa_enabled || !$settings->wa_gateway_token) {
            Log::warning('WA Service: Sending skipped (Disabled or missing token).');
            return false;
        }

        // Clean phone number (ensure it starts with country code, etc. - simple version for now)
        $phone = self::formatPhoneNumber($phone);

        try {
            $provider = $settings->wa_gateway_provider;
            $url = $provider === 'custom' ? $settings->wa_gateway_url : 'https://api.fonnte.com/send';
            $token = $settings->wa_gateway_token;
            
            $phoneParam = $provider === 'custom' ? $settings->wa_gateway_phone_param : 'target';
            $messageParam = $provider === 'custom' ? $settings->wa_gateway_message_param : 'message';

            $payload = [
                $phoneParam => $phone,
                $messageParam => $message,
            ];

            // Fonnte specific additional params
            if ($provider === 'fonnte') {
                $payload['delay'] = '2';
                $payload['countryCode'] = '62'; // Default to Indonesia
            }

            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post($url, $payload);

            if ($response->successful()) {
                return true;
            }

            Log::error('WA Service Error: ' . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error('WA Service Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Format phone number to international format (62...)
     */
    protected static function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (str_starts_with($phone, '8')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }
}
