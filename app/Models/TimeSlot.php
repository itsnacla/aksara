<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    protected $fillable = [
        'nama_jam',
        'waktu_mulai',
        'waktu_selesai',
        'is_istirahat',
        'urutan',
    ];

    protected $casts = [
        'is_istirahat' => 'boolean',
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
    ];

    public function levels()
    {
        return $this->belongsToMany(Level::class);
    }
}
