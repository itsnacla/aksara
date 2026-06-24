<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $student_id
 * @property int $study_group_id
 * @property int|null $schedule_id
 * @property string $status
 * @property string|null $check_in
 * @property string|null $check_out
 * @property \Illuminate\Support\Carbon $tanggal
 * @property string|null $catatan
 * @property \Illuminate\Support\Carbon|null $wa_sent_at
 */
#[Fillable([
    'student_id',
    'study_group_id',
    'schedule_id',
    'status',
    'check_in',
    'check_out',
    'tanggal',
    'catatan',
    'wa_sent_at',
])]
class Attendance extends Model
{
    protected $casts = [
        'wa_sent_at' => 'datetime',
        'tanggal' => 'date',
    ];

    protected static function booted()
    {
        static::saving(function ($attendance) {
            if ($attendance->status === 'hadir' && empty($attendance->check_in)) {
                $attendance->check_in = now()->format('H:i:s');
            }
        });

        static::created(function ($attendance) {
            event(new \App\Events\AttendanceLogged(
                $attendance,
                $attendance->student->user->name ?? 'Siswa',
                $attendance->status
            ));
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function studyGroup(): BelongsTo
    {
        return $this->belongsTo(StudyGroup::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }
}
