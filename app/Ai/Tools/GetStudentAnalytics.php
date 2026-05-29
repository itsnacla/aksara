<?php

namespace App\Ai\Tools;

use App\Models\Grade;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetStudentAnalytics implements Tool
{
    public function __construct(protected ?User $user = null) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Dapatkan analytics siswa: top performers, ranking, average attendance, problem students, dll.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        if (!$this->user) {
            return 'Error: User context missing.';
        }

        $analyticsType = $request['type'] ?? 'all'; // top_performers, low_performers, top_attendance, low_attendance, class_ranking
        $studyGroupId = $request['study_group_id'] ?? null;
        $roleName = $this->user->roles->first()?->name ?? 'siswa';
        $limit = $request['limit'] ?? 10;

        // Security check
        if (!str_contains($roleName, 'admin') && !str_contains($roleName, 'staff') && !str_contains($roleName, 'guru')) {
            return 'Anda tidak memiliki akses untuk melihat analytics siswa.';
        }

        // For guru, only their class
        if (str_contains($roleName, 'guru')) {
            $teacher = $this->user->teacher;
            if (!$teacher) {
                return 'Data guru tidak ditemukan.';
            }
            if (!$studyGroupId) {
                $studyGroupId = $teacher->studyGroups()
                    ->where('wali_kelas_id', $teacher->id)
                    ->first()?->id;
            }
        }

        $result = [];

        // Top Performers - highest average grade
        if (in_array($analyticsType, ['all', 'top_performers'])) {
            $topPerformers = Grade::query()
                ->selectRaw('student_id, AVG((nilai_tugas + nilai_uts + nilai_uas) / 3) as avg_nilai')
                ->with(['student.user'])
                ->when($studyGroupId, fn($q) => $q->whereHas('student.studyGroups', fn($sq) => $sq->where('study_groups.id', $studyGroupId)))
                ->groupBy('student_id')
                ->orderByDesc('avg_nilai')
                ->limit($limit)
                ->get();

            $result['top_performers'] = $topPerformers->map(fn($g) => [
                'nama' => $g->student?->user?->name,
                'nisn' => $g->student?->nisn,
                'rata_rata_nilai' => round($g->avg_nilai, 2),
            ])->toArray();
        }

        // Low Performers - lowest average grade
        if (in_array($analyticsType, ['all', 'low_performers'])) {
            $lowPerformers = Grade::query()
                ->selectRaw('student_id, AVG((nilai_tugas + nilai_uts + nilai_uas) / 3) as avg_nilai')
                ->with(['student.user'])
                ->when($studyGroupId, fn($q) => $q->whereHas('student.studyGroups', fn($sq) => $sq->where('study_groups.id', $studyGroupId)))
                ->groupBy('student_id')
                ->orderBy('avg_nilai', 'asc')
                ->limit($limit)
                ->get();

            $result['low_performers'] = $lowPerformers->map(fn($g) => [
                'nama' => $g->student?->user?->name,
                'nisn' => $g->student?->nisn,
                'rata_rata_nilai' => round($g->avg_nilai, 2),
                'status_warning' => $g->avg_nilai < 60 ? '⚠️ PERLU BIMBINGAN' : '⚡ MONITOR',
            ])->toArray();
        }

        // Top Attendance - highest attendance percentage
        if (in_array($analyticsType, ['all', 'top_attendance'])) {
            $topAttendance = Attendance::query()
                ->selectRaw('student_id, 
                    ROUND(SUM(CASE WHEN status = "H" THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as attendance_pct')
                ->with(['student.user'])
                ->when($studyGroupId, fn($q) => $q->where('study_group_id', $studyGroupId))
                ->groupBy('student_id')
                ->orderByDesc('attendance_pct')
                ->limit($limit)
                ->get();

            $result['top_attendance'] = $topAttendance->map(fn($a) => [
                'nama' => $a->student?->user?->name,
                'nisn' => $a->student?->nisn,
                'presensi_pct' => $a->attendance_pct . '%',
            ])->toArray();
        }

        // Low Attendance - lowest attendance percentage (below 80% warning threshold)
        if (in_array($analyticsType, ['all', 'low_attendance'])) {
            $lowAttendance = Attendance::query()
                ->selectRaw('student_id, 
                    ROUND(SUM(CASE WHEN status = "H" THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as attendance_pct')
                ->with(['student.user'])
                ->when($studyGroupId, fn($q) => $q->where('study_group_id', $studyGroupId))
                ->groupBy('student_id')
                ->having('attendance_pct', '<', 80)
                ->orderBy('attendance_pct', 'asc')
                ->limit($limit)
                ->get();

            $result['low_attendance'] = $lowAttendance->map(fn($a) => [
                'nama' => $a->student?->user?->name,
                'nisn' => $a->student?->nisn,
                'presensi_pct' => $a->attendance_pct . '%',
                'status_warning' => '🚨 PERLU PERHATIAN ORANG TUA',
            ])->toArray();
        }

        // Class Ranking - ranking per kelas
        if (in_array($analyticsType, ['all', 'class_ranking']) && $studyGroupId) {
            $classRanking = Grade::query()
                ->selectRaw('student_id, AVG((nilai_tugas + nilai_uts + nilai_uas) / 3) as avg_nilai')
                ->with(['student.user'])
                ->whereHas('student.studyGroups', fn($q) => $q->where('study_groups.id', $studyGroupId))
                ->groupBy('student_id')
                ->orderByDesc('avg_nilai')
                ->get();

            $result['class_ranking'] = $classRanking->map(fn($g, $index) => [
                'ranking' => $index + 1,
                'nama' => $g->student?->user?->name,
                'nisn' => $g->student?->nisn,
                'nilai_rata_rata' => round($g->avg_nilai, 2),
            ])->toArray();
        }

        if (empty($result)) {
            return 'Tidak ada data analytics yang tersedia.';
        }

        return json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'type' => $schema->string()->description('Tipe analytics: "all", "top_performers", "low_performers", "top_attendance", "low_attendance", "class_ranking"'),
            'study_group_id' => $schema->integer()->description('ID rombel/kelas (required untuk beberapa type)'),
            'limit' => $schema->integer()->description('Jumlah hasil maksimal (default: 10)'),
        ];
    }
}
