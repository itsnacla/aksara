<?php

namespace App\Livewire;

use App\Jobs\SendWhatsAppAttendanceNotification;
use App\Jobs\SendWhatsAppCheckoutNotification;
use App\Models\Attendance;
use App\Models\SchoolSetting;
use App\Models\Student;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class QrScanStandalone extends Component
{
    public $scanned_id = '';

    public $last_scanned = null;

    public $status_message = '';

    public $status_type = ''; // success, error, warning

    public $school;

    public $isEmbedded = false;

    public $last_token = '';

    public $last_token_time = 0;

    public function mount()
    {
        $this->school = SchoolSetting::current();
    }

    // Removed updatedScannedId to prevent double-triggering

    public function processScan($token)
    {
        // Cooldown: prevent same token from being processed within 10 seconds
        if ($this->last_token === $token && (time() - $this->last_token_time) < 10) {
            return;
        }

        $this->last_token = $token;
        $this->last_token_time = time();

        try {
            $student = Student::where('nisn', $token)->with('user')->first();

            if (! $student) {
                $this->status_message = "Kartu tidak dikenali! (Token: $token)";
                $this->status_type = 'error';

                return;
            }

            // Find current active schedule for this student's rombel
            $rombelIds = $student->studyGroups()->pluck('study_groups.id');
            $today = now()->toDateString();

            // For simple attendance, we just use the current date and first rombel
            $rombelId = $rombelIds->first();

            if (! $rombelId) {
                $this->status_message = 'Siswa belum terdaftar di Rombel manapun.';
                $this->status_type = 'warning';

                return;
            }

            $attendance = Attendance::where('student_id', $student->id)
                ->where('study_group_id', $rombelId)
                ->where('tanggal', $today)
                ->first();

            if ($attendance) {
                $checkInTime = \Carbon\Carbon::parse($attendance->check_in);
                $now = now();
                
                // Jika sudah lewat 60 menit sejak check-in, anggap sebagai check-out (pulang)
                if ($now->diffInMinutes($checkInTime) >= 60) {
                    if (is_null($attendance->check_out)) {
                        $attendance->update([
                            'check_out' => $now->format('H:i:s'),
                            'catatan' => $attendance->catatan.' | Scan Pulang pada '.$now->format('H:i:s'),
                        ]);
                        $this->status_message = 'Presensi Pulang Berhasil: '.$student->user->name;
                        
                        // Dispatch Checkout WA Job
                        SendWhatsAppCheckoutNotification::dispatch($attendance);
                    } else {
                        // Sudah pernah check out hari ini
                        $attendance->update([
                            'catatan' => $attendance->catatan.' | Scan Pulang ulang pada '.$now->format('H:i:s'),
                        ]);
                        $this->status_message = 'Anda sudah melakukan presensi pulang: '.$student->user->name;
                    }
                } else {
                    $attendance->update([
                        'catatan' => $attendance->catatan.' | Scan ulang pada '.$now->format('H:i:s'),
                    ]);
                    $this->status_message = 'Presensi Berhasil (Ulang): '.$student->user->name;
                }
            } else {
                $attendance = Attendance::create([
                    'student_id' => $student->id,
                    'study_group_id' => $rombelId,
                    'tanggal' => $today,
                    'status' => 'hadir',
                    'check_in' => now()->format('H:i:s'),
                    'catatan' => 'Scan QR pada '.now()->format('H:i:s'),
                ]);
                
                // Dispatch WA Notification Job ONLY if not already sent today
                if (! $attendance->wa_sent_at) {
                    SendWhatsAppAttendanceNotification::dispatch($attendance);
                }
                
                $this->status_message = 'Presensi Masuk Berhasil: '.$student->user->name;
            }

            $this->last_scanned = [
                'name' => $student->user->name,
                'time' => now()->format('H:i:s'),
                'avatar' => $student->user->photo ? asset('storage/'.$student->user->photo) : null,
            ];

            $this->status_type = 'success';

        } catch (\Exception $e) {
            Log::error('QR Scan Error: '.$e->getMessage());
            $this->status_message = 'Terjadi kesalahan sistem.';
            $this->status_type = 'error';
        }
    }

    public function render()
    {
        $view = view('livewire.qr-scan-standalone');

        if (! $this->isEmbedded) {
            $view->layout('layouts.guest');
        }

        return $view;
    }
}
