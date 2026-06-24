<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $nama_tingkatan
 * @property string|null $fase
 * @property bool $is_last_level
 */
#[Fillable([
    'nama_tingkatan',
    'fase',
    'is_last_level',
])]
class Level extends Model
{
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
