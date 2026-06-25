<?php

namespace App\Ai\Tools;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetTodaySchedule implements Tool
{
    public function __construct(protected ?User $user = null) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Dapatkan jadwal hari ini untuk siswa, guru, atau kelas tertentu dengan detail lengkap.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        if (! $this->user) {
            return 'Error: User context missing.';
        }

        $studyGroupId = $request['study_group_id'] ?? null;
        $roleName = $this->user->roles->first()?->name ?? 'siswa';

        // Get today's day name
        $today = now();
        $dayName = $today->format('l'); // Monday, Tuesday, etc
        $dayNameId = match ($dayName) {
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
            default => $dayName,
        };

        $query = Schedule::query()
            ->with(['studyGroup.classroom', 'subject', 'teacher.user', 'dayConfig'])
            ->where('hari', $dayNameId)
            ->orWhereHas('dayConfig', fn ($q) => $q->where('nama_hari', $dayNameId))
            ->orderBy('jam_mulai', 'asc');

        // Filter berdasarkan role
        if (str_contains($roleName, 'siswa')) {
            $student = $this->user->student;
            if (! $student) {
                return 'Data siswa tidak ditemukan.';
            }
            $studyGroup = $student->currentStudyGroup();
            if (! $studyGroup) {
                return '❌ Anda belum terdaftar di kelas manapun.';
            }
            $query->where('study_group_id', $studyGroup->id);
        } elseif (str_contains($roleName, 'guru')) {
            $teacher = $this->user->teacher;
            if (! $teacher) {
                return 'Data guru tidak ditemukan.';
            }
            $query->where('teacher_id', $teacher->id);
        } elseif (str_contains($roleName, 'orang_tua')) {
            $parent = $this->user->parent;
            if (! $parent) {
                return 'Data orang tua tidak ditemukan.';
            }
            $childStudyGroups = $parent->students
                ->map(fn ($s) => $s->currentStudyGroup()?->id)
                ->filter()
                ->unique()
                ->toArray();
            $query->whereIn('study_group_id', $childStudyGroups);
        } elseif ($studyGroupId && (str_contains($roleName, 'admin') || str_contains($roleName, 'staff'))) {
            $query->where('study_group_id', $studyGroupId);
        }

        $schedules = $query->get();

        if ($schedules->isEmpty()) {
            return "Tidak ada jadwal untuk hari {$dayNameId} ({$today->format('d-m-Y')}).";
        }

        $result = $schedules->map(function ($schedule, $index) {
            $jamMulai = \DateTime::createFromFormat('H:i:s', $schedule->jam_mulai);
            $jamSelesai = \DateTime::createFromFormat('H:i:s', $schedule->jam_selesai);

            return [
                'urutan' => $index + 1,
                'jam' => ($jamMulai?->format('H:i') ?? $schedule->jam_mulai).' - '.($jamSelesai?->format('H:i') ?? $schedule->jam_selesai),
                'mapel' => $schedule->subject?->nama_mapel ?? 'N/A',
                'guru' => $schedule->teacher?->user?->name ?? 'N/A',
                'kelas' => $schedule->studyGroup?->nama_rombel ?? 'N/A',
                'ruangan' => $schedule->studyGroup?->classroom?->nama_ruangan ?? 'N/A',
                'jenis' => $schedule->jenis_jadwal ?? 'Pelajaran',
            ];
        })->toArray();

        // Sort by time
        usort($result, fn ($a, $b) => strcmp(explode(' - ', $a['jam'])[0], explode(' - ', $b['jam'])[0]));

        $responseText = "**Jadwal Hari Ini** ({$dayNameId}, {$today->format('d-m-Y')})\n\n";
        $responseText .= "| Jam | Mapel | Guru | Kelas | Ruangan |\n";
        $responseText .= "|-----|-------|------|-------|----------|\n";

        foreach ($result as $schedule) {
            $responseText .= "| {$schedule['jam']} | {$schedule['mapel']} | {$schedule['guru']} | {$schedule['kelas']} | {$schedule['ruangan']} |\n";
        }

        return $responseText."\n\n✅ Total: ".count($result).' pelajaran hari ini.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'study_group_id' => $schema->integer()->description('ID rombel/kelas (opsional, untuk admin/staff)'),
        ];
    }
}
