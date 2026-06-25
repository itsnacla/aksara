<?php

namespace App\Ai\Tools;

use App\Models\P5Group;
use App\Models\P5Project;
use App\Models\P5Theme;
use App\Models\Student;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetP5Data implements Tool
{
    public function __construct(protected ?User $user = null) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Mendapatkan informasi tentang Kurikulum Merdeka (P5): tema, proyek, dan kelompok P5 yang ada di sekolah.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        if (! $this->user) {
            return 'Error: User context missing.';
        }

        $queryType = $request['query_type'] ?? 'projects'; // 'themes', 'projects', 'my_group'
        $roleName = $this->user->roles->first()?->name ?? 'siswa';

        // Auto redirect to my_group if student asks generally
        if (str_contains($roleName, 'siswa') && $queryType !== 'themes') {
            $queryType = 'my_group';
        }

        if ($queryType === 'themes') {
            $themes = P5Theme::where('is_active', true)->get();

            return json_encode([
                'tema_aktif' => $themes->pluck('name')->toArray(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        if ($queryType === 'my_group' || str_contains($roleName, 'siswa')) {
            $studentId = null;
            if (str_contains($roleName, 'siswa')) {
                $studentId = $this->user->student?->id;
            } elseif (str_contains($roleName, 'orang_tua')) {
                $studentId = $this->user->parent?->students->first()?->id; // simplified
            }

            if (! $studentId) {
                return 'Data siswa tidak ditemukan untuk mencari kelompok P5.';
            }

            // Temukan group P5 dari relasi belongstomany
            $student = Student::with('p5Groups.project.theme', 'p5Groups.teacher.user')->find($studentId);

            if (! $student || $student->p5Groups->isEmpty()) {
                return 'Anda belum terdaftar dalam kelompok P5 manapun di tahun ajaran ini.';
            }

            $groups = $student->p5Groups->map(function ($g) {
                /** @var P5Group $g */
                return [
                    'nama_kelompok' => $g->name,
                    'koordinator' => $g->teacher?->user?->name ?? 'N/A',
                    'tema' => $g->project?->theme?->name ?? 'N/A',
                    'proyek' => $g->project?->name ?? 'N/A',
                    'fase' => $g->project?->fase ?? 'N/A',
                    'target_pencapaian' => $g->project?->target_description ?? 'N/A',
                ];
            });

            return json_encode([
                'kelompok_p5_saya' => $groups->toArray(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        // Default 'projects' (Admin/Guru/Staff)
        $projects = P5Project::with(['theme', 'levels'])->latest()->limit(20)->get()->map(function ($p) {
            /** @var P5Project $p */
            return [
                'tema' => $p->theme?->name,
                'judul_projek' => $p->name,
                'fase' => $p->fase,
                'target' => $p->target_description,
            ];
        });

        return json_encode([
            'daftar_proyek_p5' => $projects->toArray(),
            'total' => $projects->count(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query_type' => $schema->string()->description('Opsional. "themes" (daftar tema), "projects" (daftar proyek), atau "my_group" (kelompok saya).'),
        ];
    }
}
