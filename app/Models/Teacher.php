<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = [
        'user_id',
        'nip',
        'nama_guru',
        'spesialisasi',
        'is_walikelas',
        'is_kepalasekolah',
        'no_whatsapp',
    ];

    protected $casts = [
        'is_walikelas' => 'boolean',
        'is_kepalasekolah' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class, 'walikelas_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }
}
