<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property bool $is_active
 */
#[Fillable([
    'academic_year_id',
    'name',
    'is_active',
])]
class P5Theme extends Model
{
    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            if (! $model->academic_year_id) {
                $model->academic_year_id = AcademicYear::where('is_active', true)->first()?->id;
            }
        });
    }

    public function projects(): HasMany
    {
        return $this->hasMany(P5Project::class);
    }
}
