<?php

namespace App\Ai\Tools;

use App\Models\Attendance;
use App\Models\Grade;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetAcademicData implements Tool
{
    public function __construct(protected ?User $user = null) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Mendapatkan data nilai (tugas, UTS, UAS) dan ringkasan absensi siswa.';
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
        $roleName = $this->user->roles->first()?->name ?? 'siswa';

        $query = Grade::with(['subject', 'academicYear']);

        // Security logic based on existing ChatbotController
        if (str_contains($roleName, 'siswa')) {
            $student = $this->user->student;
            if (! $student) {
                return 'Data siswa tidak ditemukan.';
            }
            $query->where('student_id', $student->id);
        } elseif (str_contains($roleName, 'orang_tua')) {
            $parent = $this->user->parent;
            if (! $parent) {
                return 'Data orang tua tidak ditemukan.';
            }
            $childIds = $parent->students->pluck('id')->toArray();
            if ($studentId && in_array($studentId, $childIds)) {
                $query->where('student_id', $studentId);
            } else {
                $query->whereIn('student_id', $childIds);
            }
        } elseif (str_contains($roleName, 'guru') || str_contains($roleName, 'admin')) {
            if ($studentId) {
                $query->where('student_id', $studentId);
            } else {
                return 'Mohon tentukan ID siswa untuk melihat nilai.';
            }
        }

        $grades = $query->latest()->limit(10)->get()->map(fn ($g) => [
            'mapel' => $g->subject->nama_mapel ?? 'N/A',
            'tugas' => $g->nilai_tugas,
            'uts' => $g->nilai_uts,
            'uas' => $g->nilai_uas,
            'tahun' => $g->academicYear->tahun_ajaran ?? 'N/A',
        ]);

        // Attendance summary
        $attQuery = Attendance::query();
        if (str_contains($roleName, 'siswa')) {
            $attQuery->where('student_id', $this->user->student->id);
        } elseif (str_contains($roleName, 'orang_tua')) {
            $attQuery->whereIn('student_id', $this->user->parent->students->pluck('id')->toArray());
        } elseif ($studentId) {
            $attQuery->where('student_id', $studentId);
        }

        $attendance = $attQuery->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->get();

        return json_encode([
            'grades' => $grades,
            'attendance_summary' => $attendance,
        ], JSON_PRETTY_PRINT);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'student_id' => $schema->integer()->description('ID internal siswa (opsional jika siswa menanyakan miliknya sendiri)'),
        ];
    }
}
