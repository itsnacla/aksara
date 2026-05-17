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
    'name',
    'is_active',
])]
class P5Theme extends Model
{
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(P5Project::class);
    }
}
