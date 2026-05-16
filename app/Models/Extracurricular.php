<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $nama_ekskul
 * @property string|null $kategori
 * @property int|null $nilai_minimum
 * @property string|null $pembina
 * @property string|null $deskripsi
 */
#[Fillable([
    'nama_ekskul',
    'kategori',
    'nilai_minimum',
    'pembina',
    'deskripsi',
])]
class Extracurricular extends Model
{
}
