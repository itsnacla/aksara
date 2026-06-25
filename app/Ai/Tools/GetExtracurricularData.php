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
        if (! $this->user) {
            return 'Error: User context missing.';
        }

        $studentId = $request['student_id'] ?? null;
        $extracurricularId = $request['extracurricular_id'] ?? null;
        $viewType = $request['view_type'] ?? 'all'; // 'all', 'my_extracurricular', 'student_grades'
        $roleName = $this->user->roles->first()?->name ?? 'siswa';

        $result = [];

        // 1. Get student's enrolled extracurriculars if requested or if they are a student
        if ($viewType === 'student_grades' || $viewType === 'my_extracurricular' || str_contains($roleName, 'siswa')) {
            if (str_contains($roleName, 'siswa')) {
                $studentId = $this->user->student?->id;
            }

            if ($studentId) {
                $grades = ExtracurricularGrade::query()
                    ->with(['student.user', 'extracurricular'])
                    ->where('student_id', $studentId)
                    ->get()
                    ->map(function ($g) {
                        /** @var ExtracurricularGrade $g */
                        return [
                            'ekstrakurikuler' => $g->extracurricular?->nama_ekstrakurikuler,
                            'prestasi' => $g->prestasi,
                            'nilai_rata_rata' => $g->nilai_rata_rata,
                            'status' => $g->status ?? 'active',
                        ];
                    });
                $result['student_extracurriculars'] = $grades;
            }
        }

        // 2. Get list of all available extracurriculars if requested, OR if the student doesn't have any enrolled
        if ($viewType === 'all' || (str_contains($roleName, 'siswa') && empty($result['student_extracurriculars']))) {
            $extracurriculars = Extracurricular::query()
                ->with(['teacher.user'])
                ->get()
                ->map(function ($e) {
                    /** @var Extracurricular $e */
                    return [
                        'id' => $e->id,
                        'nama_ekstrakurikuler' => $e->nama_ekstrakurikuler,
                        'deskripsi' => $e->deskripsi,
                        'pembina' => $e->teacher?->user?->name,
                        'kategori' => $e->kategori ?? 'N/A',
                    ];
                });

            $result['available_extracurriculars'] = $extracurriculars;
            $result['total_available'] = $extracurriculars->count();
        }

        return json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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
