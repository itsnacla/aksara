<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $fillable = [
        'nama_tingkatan',
        'is_last_level',
    ];

    protected $casts = [
        'is_last_level' => 'boolean',
    ];

    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }

    public function timeSlots()
    {
        return $this->belongsToMany(TimeSlot::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class);
    }
}
