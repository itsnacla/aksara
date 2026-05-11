<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    protected $fillable = [
        'nama_ruangan',
    ];

    public function studyGroups()
    {
        return $this->hasMany(StudyGroup::class);
    }
}
