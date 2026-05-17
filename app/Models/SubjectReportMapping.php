<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $kurikulum
 * @property int $level_id
 * @property int $subject_id
 * @property string $nama_lokal
 * @property int $no_urut
 */
#[Fillable([
    'kurikulum',
    'level_id',
    'subject_id',
    'nama_lokal',
    'no_urut',
])]
class SubjectReportMapping extends Model
{
    /**
     * Get the level/class grade that this mapping belongs to.
     */
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'level_id');
    }

    /**
     * Get the global subject that this mapping belongs to.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
