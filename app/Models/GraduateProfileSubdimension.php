<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $graduate_profile_id
 * @property string $subdimensi
 */
#[Fillable([
    'graduate_profile_id',
    'subdimensi',
])]
class GraduateProfileSubdimension extends Model
{
    public function graduateProfile(): BelongsTo
    {
        return $this->belongsTo(GraduateProfile::class);
    }
}
