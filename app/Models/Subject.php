<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $table = 'subjects';

    protected $fillable = [
        'nama_mapel',
        'kode_mapel',
    ];

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }
}