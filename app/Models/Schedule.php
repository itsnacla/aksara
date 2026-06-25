<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $study_group_id
 * @property int $subject_id
 * @property int $teacher_id
 * @property string $hari
 * @property int $start_time_slot_id
 * @property int $end_time_slot_id
 */
#[Fillable([
    'study_group_id',
    'subject_id',
    'teacher_id',
    'hari',
    'start_time_slot_id',
    'end_time_slot_id',
])]
class Schedule extends Model
{
    public function studyGroup()
    {
        return $this->belongsTo(StudyGroup::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function startTimeSlot()
    {
        return $this->belongsTo(TimeSlot::class, 'start_time_slot_id');
    }

    public function endTimeSlot()
    {
        return $this->belongsTo(TimeSlot::class, 'end_time_slot_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
