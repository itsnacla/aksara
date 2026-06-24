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
        // Prevent duplicate notifications
        $this->attendance->refresh();
        if ($this->attendance->wa_sent_at) {
            return;
        }

        $settings = SchoolSetting::current();

        if (!$settings->is_wa_enabled || !$settings->wa_notify_attendance) {
            return;
        }

        $student = $this->attendance->student;
        $parent = $student->parent;

        if (!$parent || !$parent->no_whatsapp) {
            return;
        }

        $name = $student->user->name;
        $status = $this->attendance->status;
        $time = $this->attendance->created_at->format('H:i');
        $date = $this->attendance->created_at->isoFormat('dddd, D MMMM YYYY');
        
        $schoolName = strtoupper($settings->name);
        
        $message = "🔔 *NOTIFIKASI PRESENSI $schoolName*\n\n";
        $message .= "Halo Ayah/Bunda,\n";
        $message .= "Alhamdulillah, Ananda *$name* telah melakukan presensi pada:\n\n";
        $message .= "*Hari/Tgl:* $date\n";
        $message .= "⏰ *Waktu:* $time WIB\n";
        $message .= "*Status:* " . strtoupper($status) . "\n\n";
        $message .= "Semoga Ananda senantiasa semangat dalam menuntut ilmu. Aamiin.\n\n";
        $message .= "--- _Powered by Aksara | Tateta_ ---";

        $sent = \App\Services\WAService::sendMessage($parent->no_whatsapp, $message);

        if ($sent) {
            $this->attendance->update(['wa_sent_at' => now()]);
        }
    }
}
