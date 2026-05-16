<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $parent_id
 * @property string|null $nisn
 * @property string|null $nis
 * @property string $status
 * @property string|null $pob
 * @property \Illuminate\Support\Carbon|null $dob
 * @property string|null $gender
 * @property string|null $religion
 * @property string|null $phone
 * @property string|null $address
 * @property string|null $village
 * @property string|null $district
 * @property string|null $city
 * @property string|null $province
 * @property bool $lives_with_parent
 * @property string|null $previous_school
 */
#[Fillable([
    'user_id',
    'parent_id',
    'nisn',
    'nis',
    'status',
    'pob',
    'dob',
    'gender',
    'religion',
    'phone',
    'address',
    'village',
    'district',
    'city',
    'province',
    'lives_with_parent',
    'previous_school',
])]
class Student extends Model
{
    protected $casts = [
        'dob' => 'date',
        'lives_with_parent' => 'boolean',
    ];

    protected static function booted()
    {
        static::created(function ($student) {
            event(new \App\Events\StatsUpdated('student'));
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function studyGroups()
    {
        return $this->belongsToMany(StudyGroup::class, 'study_group_student');
    }

    public function currentStudyGroup()
    {
        return $this->studyGroups()
            ->whereHas('academicYear', function($q) {
                $q->where('is_active', true);
            })->first();
    }

    public function parent()
    {
        return $this->belongsTo(StudentParent::class, 'parent_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function eReports()
    {
        return $this->hasMany(EReport::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
