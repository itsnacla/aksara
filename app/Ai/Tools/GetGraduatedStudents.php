<?php

namespace App\Ai\Tools;

use App\Models\Student;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetGraduatedStudents implements Tool
{
    public function __construct(protected ?User $user = null) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Dapatkan daftar siswa yang telah lulus/graduated, termasuk status, tahun lulus, dan informasi lengkap.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        if (! $this->user) {
            return 'Error: User context missing.';
        }

        $year = $request['year'] ?? null; // Filter by graduation year
        $levelId = $request['level_id'] ?? null; // Filter by level
        $limit = $request['limit'] ?? 50;
        $roleName = $this->user->roles->first()?->name ?? 'siswa';

        // Only admin/staff dapat akses graduated students
        if (! str_contains($roleName, 'admin') && ! str_contains($roleName, 'staff')) {
            return 'Anda tidak memiliki akses untuk melihat data siswa lulus.';
        }

        $query = Student::query()
            ->with(['user', 'studyGroups.level', 'parent.user', 'studyGroups.academicYear'])
            ->where('status', 'lulus');

        if ($year) {
            $query->whereYear('updated_at', $year); // Using updated_at as proxy for graduation year
        }

        if ($levelId) {
            $query->whereHas('studyGroups', fn ($q) => $q->where('level_id', $levelId));
        }

        $graduates = $query->orderByDesc('updated_at')->limit($limit)->get();

        if ($graduates->isEmpty()) {
            return '📚 Tidak ada siswa yang lulus dengan kriteria pencarian tersebut.';
        }

        $result = $this->formatGraduates($graduates);
        $stats = $this->calculateStatistics($result);

        return json_encode([
            'total_graduated' => count($result),
            'statistics_by_year' => $stats,
            'graduates' => $result,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'year' => $schema->integer()->description('Filter by graduation year (e.g., 2024, 2025)'),
            'level_id' => $schema->integer()->description('Filter by level/tingkat (1=SD, 2=SMP, 3=SMA)'),
            'limit' => $schema->integer()->description('Jumlah hasil maksimal (default: 50)'),
        ];
    }

    private function formatGraduates($graduates): array
    {
        $result = [];
        foreach ($graduates as $student) {
            /** @var Student $student */
            $lastStudyGroup = $student->studyGroups()
                ->orderByDesc('academic_year_id')
                ->first();

            $result[] = [
                'id' => $student->id,
                'nama' => $student->user->name,
                'nisn' => $student->nisn,
                'kelas_terakhir' => $lastStudyGroup?->nama_rombel,
                'level' => $lastStudyGroup?->level?->nama_tingkatan,
                'tahun_lulus' => optional($student->updated_at)->format('Y') ?? 'N/A',
                'tanggal_lulus' => optional($student->updated_at)->format('d-m-Y') ?? 'N/A',
                'status_final' => 'Lulus',
                'orang_tua' => $student->parent?->user?->name,
            ];
        }

        return $result;
    }

    private function calculateStatistics(array $result): array
    {
        $byYear = collect($result)->groupBy('tahun_lulus');
        $stats = [];
        foreach ($byYear as $year => $grads) {
            $stats[] = [
                'tahun' => $year,
                'jumlah' => count($grads),
            ];
        }

        return $stats;
    }
}
