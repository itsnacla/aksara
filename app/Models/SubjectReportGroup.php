<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $kelompok
 * @property string $nama_kelompok
 * @property bool $is_active
 */
#[Fillable([
    'kelompok',
    'nama_kelompok',
    'is_active',
])]
class SubjectReportGroup extends Model
{
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the subjects that belong to this report group.
     */
    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class, 'subject_report_group_id');
    }
}
