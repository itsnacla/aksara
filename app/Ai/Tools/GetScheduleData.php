<?php

namespace App\Ai\Tools;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetScheduleData implements Tool
{
    public function __construct(protected ?User $user = null) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Mendapatkan jadwal pelajaran berdasarkan kelas atau guru.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        if (!$this->user) return 'Error: User context missing.';

        $roleName = $this->user->roles->first()?->name ?? 'siswa';
        $query = Schedule::with(['subject', 'studyGroup.classroom', 'teacher.user']);

        if (str_contains($roleName, 'siswa')) {
            $activeRombelId = $this->user->student->studyGroups()
                ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
                ->first()?->id ?? 0;
            $query->where('study_group_id', $activeRombelId);
        } elseif (str_contains($roleName, 'guru')) {
            $query->where('teacher_id', $this->user->teacher->id ?? 0);
        } elseif (isset($request['study_group_id'])) {
            $query->where('study_group_id', $request['study_group_id']);
        }

        $schedules = $query->get()->map(fn($s) => [
            'hari' => $s->hari,
            'jam' => "{$s->jam_mulai} - {$s->jam_selesai}",
            'mapel' => $s->subject->nama_pelajaran ?? 'N/A',
            'rombel' => $s->studyGroup->nama_rombel ?? 'N/A',
            'ruangan' => $s->studyGroup->classroom->nama_ruangan ?? 'N/A',
            'guru' => $s->teacher->user->name ?? 'N/A',
        ]);

        return json_encode($schedules, JSON_PRETTY_PRINT);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'study_group_id' => $schema->integer()->description('ID internal Rombel/Kelas (opsional)'),
        ];
    }
}
