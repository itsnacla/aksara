<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $student_id
 * @property int $academic_year_id
 * @property string $semester
 * @property string $file_path
 */
#[Table('e_reports')]
#[Fillable([
    'student_id',
    'academic_year_id',
    'semester',
    'file_path',
])]
class EReport extends Model
{
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
