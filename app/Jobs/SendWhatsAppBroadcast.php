<?php

namespace App\Jobs;

use App\Models\SchoolSetting;
use App\Services\WAService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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

        if (! $settings->is_wa_enabled) {
            return;
        }

        $finalMessage = $this->message."\n\n--- _Powered by Aksara | Tateta_ ---";

        WAService::sendMessage($this->phone, $finalMessage);
    }
}
