<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $fillable = [
        'tahun_ajaran',
        'semester',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function eReports()
    {
        return $this->hasMany(EReport::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }
}
