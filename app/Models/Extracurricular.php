<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Extracurricular extends Model
{
    protected $fillable = [
        'student_id',
        'nama_ekskul',
        'nilai_kualitatif',
        'deskripsi',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
