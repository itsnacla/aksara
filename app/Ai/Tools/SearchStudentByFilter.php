<?php

namespace App\Ai\Tools;

use App\Models\Student;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchStudentByFilter implements Tool
{
    public function __construct(protected ?User $user = null) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Cari siswa berdasarkan berbagai filter (nama, NISN, kelas, level) untuk admin, guru, atau staff.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        if (!$this->user) {
            return 'Error: User context missing.';
        }

        $roleName = $this->user->roles->first()?->name ?? 'siswa';

        // Hanya admin, staff, atau guru yang bisa search student
        if (!str_contains($roleName, 'admin') && !str_contains($roleName, 'staff') && !str_contains($roleName, 'guru')) {
            return 'Anda tidak memiliki akses untuk melakukan pencarian siswa.';
        }

        $name = $request['name'] ?? null;
        $nisn = $request['nisn'] ?? null;
        $studyGroupId = $request['study_group_id'] ?? null;
        $levelId = $request['level_id'] ?? null;
        $limit = $request['limit'] ?? 20;

        $query = Student::query()->with(['user', 'studyGroups.level', 'parent.user']);

        if ($name) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$name}%"));
        }

        if ($nisn) {
            $query->where('nisn', $nisn);
        }

        if ($studyGroupId) {
            $query->whereHas('studyGroups', fn($q) => $q->where('study_groups.id', $studyGroupId));
        }

        if ($levelId) {
            $query->whereHas('studyGroups', fn($q) => $q->where('level_id', $levelId));
        }

        // Security for guru: hanya bisa search siswa di kelas yang diwalikan
        if (str_contains($roleName, 'guru')) {
            $teacher = $this->user->teacher;
            if ($teacher && $teacher->wali_kelas_id) {
                // Guru hanya bisa search di kelas perwalian-nya
                if (!$studyGroupId) {
                    // Jika tidak ada filter kelas, gunakan kelas perwalian guru
                    $studyGroupId = $teacher->wali_kelas_id;
                }
                $query->whereHas('studyGroups', fn($q) => $q->where('study_groups.id', $studyGroupId));
            }
        }

        $students = $query->limit($limit)->get();

        if ($students->isEmpty()) {
            return 'Tidak ada siswa yang ditemukan dengan kriteria pencarian tersebut.';
        }

        $result = $students->map(function ($student) {
            $studyGroup = $student->currentStudyGroup();
            return [
                'id' => $student->id,
                'nama' => $student->user->name,
                'nisn' => $student->nisn,
                'kelas' => $studyGroup?->nama_rombel,
                'level' => $studyGroup?->level?->nama_tingkatan,
                'orang_tua' => $student->parent?->user?->name,
                'email' => $student->user->email,
                'status' => $student->status ?? 'aktif',
            ];
        })->toArray();

        return json_encode([
            'total_found' => count($result),
            'limit' => $limit,
            'students' => $result,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Nama siswa atau potongan nama'),
            'nisn' => $schema->string()->description('Nomor Induk Siswa Nasional'),
            'study_group_id' => $schema->integer()->description('ID rombel/kelas'),
            'level_id' => $schema->integer()->description('ID tingkatan (e.g., 1, 2, 3 untuk SD, SMP, SMA)'),
            'limit' => $schema->integer()->description('Jumlah hasil maksimal (default: 20)'),
        ];
    }
}
