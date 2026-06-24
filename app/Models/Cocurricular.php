<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $tema
 * @property string $nama_projek
 * @property string $fase
 * @property string|null $deskripsi
 * @property string|null $tahun_ajaran
 */
#[Fillable([
    'tema',
    'nama_projek',
    'fase',
    'deskripsi',
    'tahun_ajaran',
])]
class Cocurricular extends Model
{
    //
}
