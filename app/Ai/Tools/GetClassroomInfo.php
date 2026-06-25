<?php

namespace App\Ai\Tools;

use App\Models\StudyGroup;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetClassroomInfo implements Tool
{
    public function __construct(protected ?User $user = null) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Dapatkan informasi detail kelas/rombel termasuk wali kelas, jumlah siswa, dan daftar siswa.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        if (! $this->user) {
            return 'Error: User context missing.';
        }

        $classroomId = $request['classroom_id'] ?? null;
        $studyGroupId = $request['study_group_id'] ?? null;
        $className = $request['class_name'] ?? null;
        $roleName = $this->user->roles->first()?->name ?? 'siswa';

        $query = StudyGroup::query()->with(['classroom', 'level', 'teacher', 'students.user', 'academicYear']);

        // Security: Guru hanya bisa lihat kelas miliknya
        if (str_contains($roleName, 'guru')) {
            $teacher = $this->user->teacher;
            if (! $teacher) {
                return 'Data guru tidak ditemukan.';
            }

            if ($studyGroupId) {
                $query->where('walikelas_id', $teacher->id)->where('id', $studyGroupId);
            } else {
                $query->where('walikelas_id', $teacher->id);
            }
        } elseif (str_contains($roleName, 'siswa')) {
            $student = $this->user->student;
            if (! $student) {
                return 'Data siswa tidak ditemukan.';
            }
            $studyGroup = $student->currentStudyGroup();
            if (! $studyGroup) {
                return 'Siswa belum terdaftar di kelas manapun.';
            }
            $query->where('id', $studyGroup->id);
        } else {
            // Admin/Staff: Apply filter jika ada
            if ($studyGroupId) {
                $query->where('id', $studyGroupId);
            } elseif ($classroomId) {
                $query->where('classroom_id', $classroomId);
            } elseif ($className) {
                $query->whereHas('classroom', fn ($q) => $q->where('nama_ruangan', 'like', "%{$className}%"));
            }
        }

        $studyGroups = $query->get();

        if ($studyGroups->isEmpty()) {
            return 'Kelas tidak ditemukan atau Anda tidak memiliki akses.';
        }

        $classrooms = $studyGroups->map(function ($sg) {
            return [
                'id' => $sg->id,
                'nama_rombel' => $sg->nama_rombel,
                'classroom' => $sg->classroom?->nama_ruangan,
                'level' => $sg->level?->nama_tingkatan,
                'wali_kelas' => $sg->teacher?->user?->name,
                'jumlah_siswa' => $sg->students->count(),
                'tahun_ajaran' => $sg->academicYear?->tahun_ajaran,
                'siswa' => $sg->students->map(fn ($s) => [
                    'nama' => $s->user->name,
                    'nisn' => $s->nisn,
                ])->toArray(),
            ];
        });

        return json_encode($classrooms, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'study_group_id' => $schema->integer()->description('ID rombel/kelas (opsional)'),
            'classroom_id' => $schema->integer()->description('ID ruangan kelas (opsional)'),
            'class_name' => $schema->string()->description('Nama kelas/rombel (opsional, untuk pencarian)'),
        ];
    }
}
