<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $no_whatsapp
 * @property string|null $hubungan
 * @property string|null $father_name
 * @property string|null $mother_name
 * @property string|null $father_occupation
 * @property string|null $mother_occupation
 * @property string|null $address
 * @property string|null $village
 * @property string|null $district
 * @property string|null $city
 * @property string|null $province
 * @property string|null $guardian_name
 * @property string|null $guardian_occupation
 * @property string|null $guardian_address
 */
#[Table('parents')]
#[Fillable([
    'user_id',
    'no_whatsapp',
    'hubungan',
    'father_name',
    'mother_name',
    'father_occupation',
    'mother_occupation',
    'address',
    'village',
    'district',
    'city',
    'province',
    'guardian_name',
    'guardian_occupation',
    'guardian_address',
])]
class StudentParent extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'parent_id');
    }
}
