<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $nama_mapel
 * @property string $kode_mapel
 * @property bool $is_umum
 * @property int $total_jp
 * @property int $kkm
 * @property int|null $level_id
 * @property bool $is_one_day_finish
 * @property int $scheduling_priority
 */
#[Fillable([
    'nama_mapel',
    'kode_mapel',
    'is_umum',
    'total_jp',
    'kkm',
    'level_id',
    'is_one_day_finish',
    'scheduling_priority',
])]
class Subject extends Model
{
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->kode_mapel)) {
                $name = $model->nama_mapel;
                
                $mappings = [
                    'Ilmu Pengetahuan Alam dan Sosial' => 'IPAS',
                    'Ilmu Pengetahuan Alam' => 'IPA',
                    'Ilmu Pengetahuan Sosial' => 'IPS',
                    'Matematika' => 'MTK',
                    'Pendidikan Jasmani, Olahraga dan Kesehatan' => 'PJOK',
                    'Seni Budaya dan Prakarya' => 'SBDP',
                ];
                
                $prefix = '';
                foreach ($mappings as $key => $val) {
                    if (stripos($name, $key) !== false) {
                        $prefix = $val;
                        break;
                    }
                }
                
                if (empty($prefix)) {
                    $prefix = str_ireplace(['Pendidikan', 'Bahasa'], ['Pend.', 'Bhs.'], $name);
                }
                
                $latest = static::where('kode_mapel', 'like', $prefix . '%')->orderBy('kode_mapel', 'desc')->first();
                $number = $latest ? (int)filter_var($latest->kode_mapel, FILTER_SANITIZE_NUMBER_INT) + 1 : 1;
                
                $model->kode_mapel = $prefix . ' ' . str_pad($number, 2, '0', STR_PAD_LEFT);
            }
        });
    }

    protected $casts = [
        'is_umum' => 'boolean',
        'kkm' => 'integer',
        'total_jp' => 'integer',
        'is_one_day_finish' => 'boolean',
        'scheduling_priority' => 'integer',
    ];

    public function levels()
    {
        return $this->belongsToMany(Level::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
