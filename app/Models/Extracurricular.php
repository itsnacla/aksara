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
 * @property array|null $hari_pelaksanaan
 */
#[Fillable([
    'nama_ekskul',
    'kategori',
    'nilai_minimum',
    'coordinator_user_id',
    'deskripsi',
    'hari_pelaksanaan',
])]
class Extracurricular extends Model
{
    protected function casts(): array
    {
        return [
            'hari_pelaksanaan' => 'array',
        ];
    }

    protected static function booted()
    {
        static::saved(function ($model) {
            // When an extracurricular is saved and it is "wajib", ensure all active students are enrolled.
            if ($model->kategori === 'wajib') {
                $studentIds = \App\Models\Student::where('status', 'aktif')->pluck('id');
                if ($studentIds->isNotEmpty()) {
                    $model->students()->syncWithoutDetaching($studentIds);
                }
            }
        });
    }

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
