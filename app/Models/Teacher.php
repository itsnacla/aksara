<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $gelar_depan
 * @property string|null $gelar_belakang
 * @property string|null $nip
 * @property string $kode_guru
 * @property bool $is_walikelas
 * @property string $status
 * @property bool $is_kepalasekolah
 * @property string|null $no_whatsapp
 */
#[Fillable([
    'user_id',
    'gelar_depan',
    'gelar_belakang',
    'nip',
    'kode_guru',
    'is_walikelas',
    'status',
    'is_kepalasekolah',
    'no_whatsapp',
])]
class Teacher extends Model
{
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->kode_guru)) {
                $name = $model->user->name ?? 'Guru';
                
                $cleanName = explode(',', $name)[0];
                
                $frontTitles = [
                    'Drs\.', 'Dra\.', 'Ir\.', 'H\.', 'Hj\.', 'Prof\.', 'Dr\.', 'Pdt\.', 'St\.'
                ];
                
                $backTitles = [
                    'S\.Pd\.I', 'S\.Pd', 'M\.Pd', 'S\.Kom', 'S\.T', 'S\.H', 'S\.Si', 'M\.Si', 
                    'S\.E', 'M\.E', 'S\.Sos', 'Gr\.', 'Gr', 'L\.c', 'M\.A', 'B\.A'
                ];

                $allTitles = array_merge($frontTitles, $backTitles);
                
                foreach ($allTitles as $title) {
                    $cleanName = preg_replace('/(?i)\b' . $title . '\b/', '', $cleanName);
                    if (str_ends_with($title, '\.')) {
                        $cleanName = preg_replace('/(?i)' . $title . '/', '', $cleanName);
                    }
                }
                
                $cleanName = trim(preg_replace('/[^a-zA-Z\s]/', '', $cleanName));
                $cleanName = preg_replace('/\s+/', ' ', $cleanName);
                
                $words = explode(' ', $cleanName);
                $prefix = '';
                foreach ($words as $w) {
                    if (!empty($w)) {
                        $prefix .= strtoupper(substr($w, 0, 1));
                    }
                }
                
                if (empty($prefix)) $prefix = 'G';

                $latest = static::where('kode_guru', 'like', $prefix . '%')->orderBy('kode_guru', 'desc')->first();
                $number = $latest ? (int)filter_var($latest->kode_guru, FILTER_SANITIZE_NUMBER_INT) + 1 : 1;
                $model->kode_guru = $prefix . ' ' . str_pad($number, 2, '0', STR_PAD_LEFT);
            }
        });

        static::saving(function ($teacher) {
            if ($teacher->is_kepalasekolah) {
                static::where('id', '!=', $teacher->id)->update(['is_kepalasekolah' => false]);
            }
        });
    }

    protected $casts = [
        'is_walikelas' => 'boolean',
        'is_kepalasekolah' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function studyGroups()
    {
        return $this->hasMany(StudyGroup::class, 'walikelas_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_teacher');
    }
}
