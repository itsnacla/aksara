<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $student_id
 * @property int|null $attendance_id
 * @property string $notification_type
 * @property string $title
 * @property string $message
 * @property bool $is_sent
 * @property bool $is_read
 * @property \Illuminate\Support\Carbon|null $sent_at
 * @property \Illuminate\Support\Carbon|null $read_at
 */
#[Fillable([
    'student_id',
    'attendance_id',
    'notification_type',
    'title',
    'message',
    'is_sent',
    'is_read',
    'sent_at',
    'read_at',
])]
class Notification extends Model
{
    protected $casts = [
        'is_sent' => 'boolean',
        'is_read' => 'boolean',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
