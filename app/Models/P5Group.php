<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int $p5_project_id
 * @property int $level_id
 * @property int $teacher_id
 * @property string $name
 */
#[Fillable([
    'p5_project_id',
    'level_id',
    'teacher_id',
    'name',
])]
class P5Group extends Model
{
    public function project(): BelongsTo
    {
        return $this->belongsTo(P5Project::class, 'p5_project_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'p5_group_student')->withTimestamps();
    }
}
