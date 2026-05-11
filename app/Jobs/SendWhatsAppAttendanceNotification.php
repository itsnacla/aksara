<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Models\SchoolSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWhatsAppAttendanceNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $attendance;

    public function __construct(Attendance $attendance)
    {
        $this->attendance = $attendance;
    }

    public function handle(): void
    {
        $settings = SchoolSetting::current();

        if (!$settings->is_wa_enabled || !$settings->wa_gateway_token) {
            return;
        }

        $student = $this->attendance->student;
        $parent = $student->parent;

        if (!$parent || !$parent->no_whatsapp) {
            return;
        }

        $name = $student->user->name;
        $status = $this->attendance->status;
        $time = now()->format('H:i');
        $date = now()->isoFormat('dddd, D MMMM YYYY');
        
        $schoolName = strtoupper($settings->name);
        
        $message = "🔔 *NOTIFIKASI PRESENSI $schoolName*\n\n";
        $message .= "Halo Ayah/Bunda,\n";
        $message .= "Alhamdulillah, Ananda *$name* telah melakukan presensi pada:\n\n";
        $message .= "📅 *Hari/Tgl:* $date\n";
        $message .= "⏰ *Waktu:* $time WIB\n";
        $message .= "📍 *Status:* " . strtoupper($status) . "\n\n";
        $message .= "Semoga Ananda senantiasa semangat dalam menuntut ilmu. Aamiin.\n\n";
        $message .= "--- _Powered by Aksara | Tateta_ ---";

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

            $response = Http::withHeaders([
                'Authorization' => $settings->wa_gateway_token,
            ])->post($url, [
                $phoneParam => $parent->no_whatsapp,
                $messageParam => $message,
                'delay' => '2',
            ]);

            if (!$response->successful()) {
                Log::error('WA Notification Failed: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('WA Notification Error: ' . $e->getMessage());
        }
    }
}
