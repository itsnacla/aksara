<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Models\SchoolSetting;
use App\Services\WAService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsAppCheckoutNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $attendance;

    public function __construct(Attendance $attendance)
    {
        $this->attendance = $attendance;
    }

    public function handle(): void
    {
        // For checkout, we use check_out instead of wa_sent_at to prevent duplicates.
        // The check_out is set right before dispatching this job.
        // We will assume that if check_out is not null, it's valid to send, but we rely on Livewire to only dispatch ONCE.

        $settings = SchoolSetting::current();

        if (! $settings->is_wa_enabled || ! $settings->wa_notify_attendance) {
            return;
        }

        $student = $this->attendance->student;
        $parent = $student->parent;

        if (! $parent || ! $parent->no_whatsapp) {
            return;
        }

        $name = $student->user->name;
        $status = $this->attendance->status;
        $time = $this->attendance->created_at->format('H:i');
        $date = $this->attendance->created_at->isoFormat('dddd, D MMMM YYYY');

        $schoolName = strtoupper($settings->name);

        $message = "🔔 *NOTIFIKASI KEPULANGAN $schoolName*\n\n";
        $message .= "Halo Ayah/Bunda,\n";
        $message .= "Alhamdulillah, Ananda *$name* telah selesai mengikuti kegiatan belajar dan melakukan presensi *PULANG* pada:\n\n";
        $message .= "*Hari/Tgl:* $date\n";
        $message .= "⏰ *Waktu:* $time WIB\n\n";
        $message .= "Semoga Ananda senantiasa semangat dalam menuntut ilmu. Aamiin.\n\n";
        $message .= '--- _Powered by Aksara | Tateta_ ---';

        $sent = WAService::sendMessage($parent->no_whatsapp, $message);

        // Fallback: Jika gagal mengirim pesan biasa (kemungkinan Jendela 24 jam tertutup), gunakan Template
        if (!$sent) {
            $parameters = [
                $schoolName,
                $name,
                $date,
                $time,
                'PULANG'
            ];
            $sent = WAService::sendTemplateMessage($parent->no_whatsapp, 'notifikasi_presensi', $parameters);
        }
    }
}
