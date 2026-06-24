<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $nama_rombel
 * @property int $level_id
 * @property int $classroom_id
 * @property int $academic_year_id
 * @property int|null $walikelas_id
 */
#[Fillable([
    'nama_rombel',
    'level_id',
    'classroom_id',
    'academic_year_id',
    'walikelas_id',
])]
class StudyGroup extends Model
{
    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->academic_year_id) {
                $model->academic_year_id = \App\Models\AcademicYear::where('is_active', true)->first()?->id;
            }
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
