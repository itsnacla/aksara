<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $dimensi
 * @property string $subdimensi
 */
#[Fillable([
    'dimensi',
    'subdimensi',
])]
class GraduateProfile extends Model
{
    //
}
