<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $nama_ekskul
 * @property string|null $kategori
 * @property int|null $nilai_minimum
 * @property int|null $coordinator_user_id
 * @property string|null $deskripsi
 */
#[Fillable([
    'nama_ekskul',
    'kategori',
    'nilai_minimum',
    'coordinator_user_id',
    'deskripsi',
])]
class Extracurricular extends Model
{
    public function students(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'extracurricular_student')->withTimestamps();
    }

    public function grades(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ExtracurricularGrade::class);
    }

    public function coordinator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinator_user_id');
    }
}
