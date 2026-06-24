<?php

namespace App\Ai\Tools;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetExamSchedule implements Tool
{
    public function __construct(protected ?User $user = null) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Dapatkan jadwal ujian/tes (pelajaran, waktu, ruangan) untuk siswa, guru, atau kelas.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        if (!$this->user) {
            return 'Error: User context missing.';
        }

        $studyGroupId = $request['study_group_id'] ?? null;
        $scheduleType = $request['schedule_type'] ?? 'all'; // 'all', 'exam', 'regular'
        $roleName = $this->user->roles->first()?->name ?? 'siswa';

        $query = Schedule::query()->with(['studyGroup.classroom', 'studyGroup.academicYear', 'subject', 'teacher.user', 'dayConfig']);

        // Filter berdasarkan role
        if (str_contains($roleName, 'siswa')) {
            $student = $this->user->student;
            if (!$student) {
                return 'Data siswa tidak ditemukan.';
            }
            $studyGroup = $student->currentStudyGroup();
            if (!$studyGroup) {
                return 'Siswa belum terdaftar di kelas manapun.';
            }
            $query->where('study_group_id', $studyGroup->id);
        } elseif (str_contains($roleName, 'guru')) {
            $teacher = $this->user->teacher;
            if (!$teacher) {
                return 'Data guru tidak ditemukan.';
            }
            $query->where('teacher_id', $teacher->id);
        } elseif (str_contains($roleName, 'orang_tua')) {
            $parent = $this->user->parent;
            if (!$parent) {
                return 'Data orang tua tidak ditemukan.';
            }
            $childStudyGroups = $parent->students->map(fn($s) => $s->currentStudyGroup()?->id)->filter()->unique();
            $query->whereIn('study_group_id', $childStudyGroups);
        } elseif ($studyGroupId && (str_contains($roleName, 'admin') || str_contains($roleName, 'staff'))) {
            $query->where('study_group_id', $studyGroupId);
        }

        // Filter by schedule type
        if ($scheduleType === 'exam') {
            $query->where('jenis_jadwal', 'ujian')->orWhere('jenis_jadwal', 'tes');
        } elseif ($scheduleType === 'regular') {
            $query->where('jenis_jadwal', 'pelajaran');
        }

        $schedules = $query->orderBy('hari', 'asc')->orderBy('jam_mulai', 'asc')->get();

        if ($schedules->isEmpty()) {
            return 'Jadwal tidak ditemukan.';
        }

        $result = $schedules->map(function ($schedule) {
            return [
                'hari' => $schedule->dayConfig?->nama_hari ?? $schedule->hari,
                'jam' => $schedule->jam_mulai . ' - ' . $schedule->jam_selesai,
                'mapel' => $schedule->subject?->nama_mapel,
                'guru' => $schedule->teacher?->user?->name,
                'kelas' => $schedule->studyGroup?->nama_rombel,
                'ruangan' => $schedule->studyGroup?->classroom?->nama_ruangan,
                'jenis' => $schedule->jenis_jadwal ?? 'pelajaran',
                'tahun_ajaran' => $schedule->studyGroup?->academicYear?->tahun_ajaran,
            ];
        })->toArray();

        return json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'study_group_id' => $schema->integer()->description('ID rombel/kelas (opsional, untuk admin/staff)'),
            'schedule_type' => $schema->string()->description('Tipe jadwal: "all" (semua), "exam" (ujian/tes), "regular" (pelajaran biasa)'),
        ];
    }
}
