<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $nama_jam
 * @property \Illuminate\Support\Carbon $waktu_mulai
 * @property \Illuminate\Support\Carbon $waktu_selesai
 * @property bool $is_istirahat
 * @property int $urutan
 */
#[Fillable([
    'nama_jam',
    'waktu_mulai',
    'waktu_selesai',
    'is_istirahat',
    'urutan',
])]
class TimeSlot extends Model
{
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
