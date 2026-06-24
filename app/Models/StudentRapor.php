<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentRapor extends Model
{
    protected $table = 'student_rapors';

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'sakit',
        'izin',
        'alpha',
        'catatan_wali_kelas',
        'is_naik',
        'kenaikan_kelas_to',
        'is_published',
    ];

    protected $casts = [
        'is_naik' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
