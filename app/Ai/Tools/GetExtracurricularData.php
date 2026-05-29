<?php

namespace App\Ai\Tools;

use App\Models\Extracurricular;
use App\Models\ExtracurricularGrade;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetExtracurricularData implements Tool
{
    public function __construct(protected ?User $user = null) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Dapatkan data ekstrakurikuler yang tersedia, peserta, dan nilai ekstrakurikuler siswa.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        if (!$this->user) {
            return 'Error: User context missing.';
        }

        $studentId = $request['student_id'] ?? null;
        $extracurricularId = $request['extracurricular_id'] ?? null;
        $viewType = $request['view_type'] ?? 'all'; // 'all', 'my_extracurricular', 'student_grades'
        $roleName = $this->user->roles->first()?->name ?? 'siswa';

        // Get extracurricular list
        if ($viewType === 'all' && (str_contains($roleName, 'admin') || str_contains($roleName, 'staff'))) {
            $extracurriculars = Extracurricular::query()
                ->with(['teacher.user', 'students.user'])
                ->get()
                ->map(function ($e) {
                    return [
                        'id' => $e->id,
                        'nama_ekstrakurikuler' => $e->nama_ekstrakurikuler,
                        'deskripsi' => $e->deskripsi,
                        'pembina' => $e->teacher?->user?->name,
                        'jumlah_siswa' => $e->students->count(),
                        'kategori' => $e->kategori ?? 'N/A',
                    ];
                });

            return json_encode([
                'extracurriculars' => $extracurriculars,
                'total' => $extracurriculars->count(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        // Get student extracurricular grades
        if ($viewType === 'student_grades' || str_contains($roleName, 'siswa')) {
            if (str_contains($roleName, 'siswa')) {
                $student = $this->user->student;
                if (!$student) {
                    return 'Data siswa tidak ditemukan.';
                }
                $studentId = $student->id;
            } elseif (!$studentId && (str_contains($roleName, 'guru') || str_contains($roleName, 'admin'))) {
                return 'Mohon tentukan student_id untuk melihat nilai ekstrakurikuler.';
            }

            $grades = ExtracurricularGrade::query()
                ->with(['student.user', 'extracurricular'])
                ->where('student_id', $studentId)
                ->get()
                ->map(function ($g) {
                    return [
                        'ekstrakurikuler' => $g->extracurricular?->nama_ekstrakurikuler,
                        'prestasi' => $g->prestasi,
                        'nilai_rata_rata' => $g->nilai_rata_rata,
                        'status' => $g->status ?? 'active',
                    ];
                });

            return json_encode([
                'student_extracurriculars' => $grades,
                'total' => $grades->count(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        // Default: List semua ekstrakurikuler
        $extracurriculars = Extracurricular::query()
            ->with(['teacher.user', 'students'])
            ->get()
            ->map(function ($e) {
                return [
                    'id' => $e->id,
                    'nama' => $e->nama_ekstrakurikuler,
                    'pembina' => $e->teacher?->user?->name,
                    'anggota' => $e->students->count(),
                ];
            });

        return json_encode($extracurriculars, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'student_id' => $schema->integer()->description('ID siswa (opsional)'),
            'extracurricular_id' => $schema->integer()->description('ID ekstrakurikuler (opsional)'),
            'view_type' => $schema->string()->description('Tipe view: "all" (list semua), "my_extracurricular" (ekstrakurikuler saya), "student_grades" (nilai ekstrakurikuler siswa)'),
        ];
    }
}
