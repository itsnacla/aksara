<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $student_id
 * @property int $learning_objective_id
 * @property int $academic_year_id
 * @property int $teacher_id
 * @property float $score
 * @property bool $is_achieved
 * @property string|null $notes
 */
#[Fillable([
    'student_id',
    'learning_objective_id',
    'academic_year_id',
    'teacher_id',
    'score',
    'is_achieved',
    'notes',
])]
class StudentGrade extends Model
{
    protected $casts = [
        'score' => 'decimal:2',
        'is_achieved' => 'boolean',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            if (! $model->academic_year_id) {
                $model->academic_year_id = AcademicYear::where('is_active', true)->first()?->id;
            }
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function learningObjective(): BelongsTo
    {
        return $this->belongsTo(LearningObjective::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
