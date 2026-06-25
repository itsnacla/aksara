<?php

namespace App\Ai\Tools;

use App\Models\LearningObjective;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetLearningObjectives implements Tool
{
    public function __construct(protected ?User $user = null) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Dapatkan tujuan pembelajaran (learning objectives) untuk mata pelajaran atau kelas tertentu.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        if (! $this->user) {
            return 'Error: User context missing.';
        }

        $subjectId = $request['subject_id'] ?? null;
        $studyGroupId = $request['study_group_id'] ?? null;
        $searchText = $request['search'] ?? null;
        $roleName = $this->user->roles->first()?->name ?? 'siswa';

        $query = LearningObjective::query()->with(['subject', 'studyGroup']);

        // Apply filters based on role
        if (str_contains($roleName, 'siswa')) {
            $student = $this->user->student;
            if (! $student) {
                return 'Data siswa tidak ditemukan.';
            }
            $studyGroup = $student->currentStudyGroup();
            if (! $studyGroup) {
                return 'Siswa belum terdaftar di kelas manapun.';
            }
            $query->where('study_group_id', $studyGroup->id);
        } elseif (str_contains($roleName, 'guru')) {
            $teacher = $this->user->teacher;
            if (! $teacher) {
                return 'Data guru tidak ditemukan.';
            }
            // Guru bisa lihat learning objectives untuk kelas-kelas yang dia ajar
            // Untuk sekarang, filter manual via subject_id atau study_group_id
        } elseif ($studyGroupId) {
            $query->where('study_group_id', $studyGroupId);
        }

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        if ($searchText) {
            $query->where('deskripsi_tujuan', 'like', "%{$searchText}%")
                ->orWhere('indikator_keberhasilan', 'like', "%{$searchText}%");
        }

        $objectives = $query->get();

        if ($objectives->isEmpty()) {
            return 'Tujuan pembelajaran tidak ditemukan.';
        }

        $result = $objectives->map(function ($obj) {
            return [
                'id' => $obj->id,
                'mapel' => $obj->subject?->nama_mapel,
                'kelas' => $obj->studyGroup?->nama_rombel,
                'tujuan' => $obj->deskripsi_tujuan,
                'indikator_keberhasilan' => $obj->indikator_keberhasilan,
                'jenis_penilaian' => $obj->jenis_penilaian ?? 'formatif',
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
            'subject_id' => $schema->integer()->description('ID mata pelajaran (opsional)'),
            'study_group_id' => $schema->integer()->description('ID rombel/kelas (opsional)'),
            'search' => $schema->string()->description('Cari tujuan pembelajaran berdasarkan text (opsional)'),
        ];
    }
}
