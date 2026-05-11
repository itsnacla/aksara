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

    protected static function booted()
    {
        static::saving(function ($academicYear) {
            if ($academicYear->is_active) {
                // Deactivate all other academic years
                static::where('id', '!=', $academicYear->id)->update(['is_active' => false]);
            }
        });
    }

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
