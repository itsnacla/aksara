<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $academic_year_id
 * @property string $dimensi
 */
#[Fillable([
    'academic_year_id',
    'dimensi',
])]
class GraduateProfile extends Model
{
    public function subdimensions(): HasMany
    {
        return $this->hasMany(GraduateProfileSubdimension::class);
    }
}
