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
 * @property string|null $nik
 * @property string|null $no_kk
 * @property string|null $no_akta_lahir
 * @property int|null $anak_ke
 * @property int|null $jumlah_saudara
 * @property float|null $tinggi_badan
 * @property float|null $berat_badan
 * @property string|null $golongan_darah
 * @property string|null $ayah_nik
 * @property string|null $ayah_nama
 * @property string|null $ayah_pendidikan
 * @property string|null $ayah_pekerjaan
 * @property string|null $ayah_penghasilan
 * @property string|null $ibu_nik
 * @property string|null $ibu_nama
 * @property string|null $ibu_pendidikan
 * @property string|null $ibu_pekerjaan
 * @property string|null $ibu_penghasilan
 * @property string|null $wali_nama
 * @property string|null $wali_pekerjaan
 * @property string|null $wali_hubungan
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
    'nik',
    'no_kk',
    'no_akta_lahir',
    'anak_ke',
    'jumlah_saudara',
    'tinggi_badan',
    'berat_badan',
    'golongan_darah',
    'ayah_nik',
    'ayah_nama',
    'ayah_pendidikan',
    'ayah_pekerjaan',
    'ayah_penghasilan',
    'ibu_nik',
    'ibu_nama',
    'ibu_pendidikan',
    'ibu_pekerjaan',
    'ibu_penghasilan',
    'wali_nama',
    'wali_pekerjaan',
    'wali_hubungan',
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

    /**
     * Get the student's active study group (rombel) for the current academic year.
     * 
     * @return \App\Models\StudyGroup|null
     */
    public function currentStudyGroup(): ?StudyGroup
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

    public function p5Groups(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(P5Group::class, 'p5_group_student')->withTimestamps();
    }
}
