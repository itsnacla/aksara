<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $p5_theme_id
 * @property int $academic_year_id
 * @property string $fase
 * @property string $name
 * @property string|null $target_description
 * @property array|null $graduate_profile
 */
#[Fillable([
    'p5_theme_id',
    'academic_year_id',
    'fase',
    'name',
    'target_description',
    'graduate_profile',
])]
class P5Project extends Model
{
    protected $casts = [
        'graduate_profile' => 'array',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            if (!$model->academic_year_id) {
                $model->academic_year_id = \App\Models\AcademicYear::where('is_active', true)->first()?->id;
            }
        });
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(P5Theme::class, 'p5_theme_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(P5Group::class);
    }
}
