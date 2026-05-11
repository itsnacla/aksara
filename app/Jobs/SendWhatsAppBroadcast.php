<?php

namespace App\Jobs;

use App\Models\SchoolSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWhatsAppBroadcast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $phone;
    public $message;

    public function __construct($phone, $message)
    {
        $this->phone = $phone;
        $this->message = $message;
    }

    public function handle(): void
    {
        $settings = SchoolSetting::current();

        if (!$settings->is_wa_enabled || !$settings->wa_gateway_token) {
            return;
        }

        try {
            $url = $settings->wa_gateway_provider === 'custom' 
                ? $settings->wa_gateway_url 
                : 'https://api.fonnte.com/send';

            $phoneParam = $settings->wa_gateway_provider === 'custom'
                ? $settings->wa_gateway_phone_param
                : 'target';

            $messageParam = $settings->wa_gateway_provider === 'custom'
                ? $settings->wa_gateway_message_param
                : 'message';

            $finalMessage = $this->message . "\n\n--- _Powered by Aksara | Tateta_ ---";

            $response = Http::withHeaders([
                'Authorization' => $settings->wa_gateway_token,
            ])->post($url, [
                $phoneParam => $this->phone,
                $messageParam => $finalMessage,
            ]);

            if (!$response->successful()) {
                Log::error('WA Broadcast Failed to ' . $this->phone . ': ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('WA Broadcast Error to ' . $this->phone . ': ' . $e->getMessage());
        }
    }
}
