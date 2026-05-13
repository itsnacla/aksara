<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentLeave extends Model
{
    protected $fillable = [
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
    ];

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
