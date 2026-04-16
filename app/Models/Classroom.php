<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    protected $fillable = [
        'level_id',
        'nama_kelas',
        'walikelas_id',
        'academic_year_id',
    ];

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function waliKelas()
    {
        return $this->belongsTo(Teacher::class, 'walikelas_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
