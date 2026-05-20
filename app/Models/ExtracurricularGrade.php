<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $extracurricular_id
 * @property int $student_id
 * @property int $academic_year_id
 * @property string $predikat
 * @property string|null $keterangan
 */
#[Fillable([
    'extracurricular_id',
    'student_id',
    'academic_year_id',
    'predikat',
    'keterangan',
])]
class ExtracurricularGrade extends Model
{
    public static array $predikatOptions = [
        'A' => 'A (Sangat Baik)',
        'B' => 'B (Baik)',
        'C' => 'C (Cukup)',
        'D' => 'D (Kurang)',
    ];

    public static array $defaultKeterangan = [
        'A' => 'Menunjukkan dedikasi dan prestasi yang sangat baik, selalu aktif berpartisipasi dalam setiap kegiatan dengan penuh semangat dan tanggung jawab.',
        'B' => 'Aktif berpartisipasi dalam kegiatan dengan sikap yang baik dan menunjukkan perkembangan yang positif.',
        'C' => 'Cukup aktif mengikuti kegiatan, namun perlu meningkatkan kedisiplinan dan konsistensi dalam berpartisipasi.',
        'D' => 'Kurang aktif dalam kegiatan, perlu bimbingan dan motivasi lebih lanjut untuk dapat berpartisipasi dengan baik.',
    ];

    public function extracurricular(): BelongsTo
    {
        return $this->belongsTo(Extracurricular::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
