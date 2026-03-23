<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EReport extends Model
{
    protected $table = 'e_reports';

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'semester',
        'file_path',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
