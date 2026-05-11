<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->kode_guru)) {
                $name = $model->user->name ?? 'Guru';
                
                // 1. Hilangkan bagian setelah koma (Gelar belakang biasanya setelah koma)
                $cleanName = explode(',', $name)[0];
                
                // 2. Daftar Gelar Depan (Prefix)
                $frontTitles = [
                    'Drs\.', 'Dra\.', 'Ir\.', 'H\.', 'Hj\.', 'Prof\.', 'Dr\.', 'Pdt\.', 'St\.'
                ];
                
                // 3. Daftar Gelar Belakang (Suffix) - untuk jaga-jaga jika tidak pakai koma
                $backTitles = [
                    'S\.Pd\.I', 'S\.Pd', 'M\.Pd', 'S\.Kom', 'S\.T', 'S\.H', 'S\.Si', 'M\.Si', 
                    'S\.E', 'M\.E', 'S\.Sos', 'Gr\.', 'Gr', 'L\.c', 'M\.A', 'B\.A'
                ];

                // Gabungkan semua gelar untuk pembersihan massal
                $allTitles = array_merge($frontTitles, $backTitles);
                
                foreach ($allTitles as $title) {
                    // Gunakan regex yang lebih fleksibel untuk menangani titik dan spasi
                    // Menghapus gelar sebagai kata utuh (case-insensitive)
                    $cleanName = preg_replace('/(?i)\b' . $title . '\b/', '', $cleanName);
                    // Tambahan: hapus jika gelar diakhiri titik tapi nempel ke kata lain
                    if (str_ends_with($title, '\.')) {
                        $cleanName = preg_replace('/(?i)' . $title . '/', '', $cleanName);
                    }
                }
                
                // 4. Bersihkan karakter non-huruf dan spasi ganda
                $cleanName = trim(preg_replace('/[^a-zA-Z\s]/', '', $cleanName));
                $cleanName = preg_replace('/\s+/', ' ', $cleanName);
                
                // 5. Ambil Inisial
                $words = explode(' ', $cleanName);
                $prefix = '';
                foreach ($words as $w) {
                    if (!empty($w)) {
                        $prefix .= strtoupper(substr($w, 0, 1));
                    }
                }
                
                // Jika setelah dibersihkan ternyata kosong (kasus langka), gunakan default
                if (empty($prefix)) $prefix = 'G';

                $latest = static::where('kode_guru', 'like', $prefix . '%')->orderBy('kode_guru', 'desc')->first();
                $number = $latest ? (int)filter_var($latest->kode_guru, FILTER_SANITIZE_NUMBER_INT) + 1 : 1;
                $model->kode_guru = $prefix . ' ' . str_pad($number, 2, '0', STR_PAD_LEFT);
            }
        });

        static::saving(function ($teacher) {
            if ($teacher->is_kepalasekolah) {
                // Set all other teachers to not be principal
                static::where('id', '!=', $teacher->id)->update(['is_kepalasekolah' => false]);
            }
        });
    }

    protected $fillable = [
        'user_id',
        'nip',
        'kode_guru',
        'is_walikelas',
        'status',
        'is_kepalasekolah',
        'no_whatsapp',
    ];

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
