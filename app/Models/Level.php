<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $fillable = [
        'nama_tingkatan',
    ];

    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }
}
