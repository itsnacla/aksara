<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $academic_year_id
 * @property string $day
 * @property bool $is_closed
 * @property array $level_ids
 * @property int|null $max_time_slot_id
 * @property int|null $mandatory_subject_id
 * @property int|null $mandatory_time_slot_id
 */
#[Fillable([
    'academic_year_id',
    'day',
    'is_closed',
    'level_ids',
    'max_time_slot_id',
    'mandatory_subject_id',
    'mandatory_time_slot_id',
])]
class DayConfig extends Model
{
    protected $casts = [
        'level_ids' => 'array',
        'is_closed' => 'boolean',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function maxTimeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class, 'max_time_slot_id');
    }

    public function mandatorySubject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'mandatory_subject_id');
    }

    public function mandatoryTimeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class, 'mandatory_time_slot_id');
    }
}
