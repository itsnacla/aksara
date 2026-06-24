<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $kurikulum
 * @property array $level_ids
 * @property int $subject_id
 * @property int $no_urut
 */
#[Fillable([
    'kurikulum',
    'level_ids',
    'subject_id',
    'no_urut',
])]
class SubjectReportMapping extends Model
{
    protected $casts = [
        'level_ids' => 'array',
    ];

    /**
     * Get the global subject that this mapping belongs to.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
