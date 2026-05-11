<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Level;
use App\Models\Classroom;
use App\Models\AcademicYear;

class StudyGroup extends Model
{
    protected $fillable = [
        'nama_rombel',
        'level_id',
        'classroom_id',
        'academic_year_id',
        'walikelas_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $level = Level::find($model->level_id)?->nama_tingkatan ?? 'N/A';
            $room = Classroom::find($model->classroom_id)?->nama_ruangan ?? 'N/A';
            
            $model->nama_rombel = "{$level} - {$room}";
        });
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function waliKelas()
    {
        return $this->belongsTo(Teacher::class, 'walikelas_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'study_group_student');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
