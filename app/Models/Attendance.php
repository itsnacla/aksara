<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'student_id',
        'study_group_id',
        'schedule_id',
        'status',
        'check_in',
        'check_out',
        'tanggal',
        'catatan',
        'wa_sent_at',
    ];

    protected $casts = [
        'wa_sent_at' => 'datetime',
        'tanggal' => 'date',
    ];

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
