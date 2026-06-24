<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $nama_ruangan
 */
#[Fillable(['nama_ruangan'])]
class Classroom extends Model
{
    public function studyGroups()
    {
        return $this->hasMany(StudyGroup::class);
    }
}
