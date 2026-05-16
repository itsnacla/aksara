<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $student_id
 * @property int $subject_id
 * @property int $teacher_id
 * @property int $academic_year_id
 * @property int $study_group_id
 * @property int $nilai_tugas
 * @property int $nilai_uts
 * @property int $nilai_uas
 */
#[Fillable([
    'student_id',
    'subject_id',
    'teacher_id',
    'academic_year_id',
    'study_group_id',
    'nilai_tugas',
    'nilai_uts',
    'nilai_uas',
])]
class Grade extends Model
{
    protected $casts = [
        'nilai_tugas' => 'integer',
        'nilai_uts' => 'integer',
        'nilai_uas' => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

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

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
