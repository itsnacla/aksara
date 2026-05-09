<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Student extends Model
{
    protected $table = 'students';

    protected $fillable = [
        'nama_siswa',
        'nisn',
        'jenis_kelamin',
        'school_class_id',
        'qr_code',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($student) {
            if (empty($student->qr_code)) {
                $student->qr_code = 'STD-' . strtoupper(Str::random(10));
            }
        });
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}