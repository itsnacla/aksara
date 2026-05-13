<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentParent extends Model
{
    protected $table = 'parents';

    protected $fillable = [
        'user_id',
        'no_whatsapp',
        'hubungan',
        'father_name',
        'mother_name',
        'father_occupation',
        'mother_occupation',
        'address',
        'village',
        'district',
        'city',
        'province',
        'guardian_name',
        'guardian_occupation',
        'guardian_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'parent_id');
    }
}
