<?php

namespace App\Ai\Tools;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetAbsentStudents implements Tool
{
    public function __construct(protected ?User $user = null) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Dapatkan daftar siswa yang bolos/absent pada periode tertentu (hari ini, minggu ini, bulan ini).';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        if (!$this->user) {
            return 'Error: User context missing.';
        }

        $period = $request['period'] ?? 'today'; // today, week, month
        $studyGroupId = $request['study_group_id'] ?? null;
        $roleName = $this->user->roles->first()?->name ?? 'siswa';

        // Parse period to date range
        $now = now();
        $dateRange = match ($period) {
            'today' => [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay(),
            ],
            'week' => [
                $now->copy()->startOfWeek(),
                $now->copy()->endOfWeek(),
            ],
            'month' => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
            ],
            default => [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay(),
            ],
        };

        $query = Attendance::query()
            ->with(['student.user', 'studyGroup'])
            ->where('status', 'A') // Alfa/Absent
            ->whereBetween('created_at', $dateRange);

        // Role-based filtering
        if (str_contains($roleName, 'guru')) {
            $teacher = $this->user->teacher;
            if (!$teacher) {
                return 'Data guru tidak ditemukan.';
            }
            // Guru hanya bisa lihat absent di kelas perwalian-nya
            $query->whereHas('studyGroup', fn($q) => $q->where('wali_kelas_id', $teacher->id));
        } elseif ($studyGroupId && (str_contains($roleName, 'admin') || str_contains($roleName, 'staff'))) {
            $query->where('study_group_id', $studyGroupId);
        } elseif (!str_contains($roleName, 'admin') && !str_contains($roleName, 'staff') && !str_contains($roleName, 'guru')) {
            return 'Anda tidak memiliki akses untuk melihat data absent siswa.';
        }

        $absents = $query->get();

        if ($absents->isEmpty()) {
            return "✅ Tidak ada siswa yang bolos pada periode {$period}.";
        }

        // Group by class for better readability
        $groupedByClass = $absents->groupBy(fn($a) => $a->studyGroup?->nama_rombel ?? 'Unknown');

        $result = [];
        foreach ($groupedByClass as $className => $absenceList) {
            $studentData = $absenceList->map(fn($a) => [
                'nama' => $a->student?->user?->name,
                'nisn' => $a->student?->nisn,
                'tanggal' => $a->created_at->format('d-m-Y'),
                'jam' => $a->created_at->format('H:i'),
            ])->toArray();

            $result[] = [
                'kelas' => $className,
                'jumlah_bolos' => count($studentData),
                'siswa' => $studentData,
            ];
        }

        return json_encode([
            'period' => $period,
            'total_absent' => $absents->count(),
            'by_class' => $result,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'period' => $schema->string()->description('Period: "today", "week", atau "month"'),
            'study_group_id' => $schema->integer()->description('ID rombel/kelas (opsional, untuk admin/staff)'),
        ];
    }
}
