<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $student_id
 * @property int $parent_id
 * @property string $type
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $end_date
 * @property string $reason
 * @property string|null $attachment
 * @property string $status
 * @property int|null $approved_by
 * @property string|null $rejection_note
 * @property int|null $study_group_id
 */
#[Fillable([
    'student_id',
    'parent_id',
    'type',
    'start_date',
    'end_date',
    'reason',
    'attachment',
    'status',
    'approved_by',
    'rejection_note',
    'study_group_id',
])]
class StudentLeave extends Model
{
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function studyGroup()
    {
        return $this->belongsTo(StudyGroup::class);
    }

    public function parent()
    {
        return $this->belongsTo(StudentParent::class, 'parent_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
