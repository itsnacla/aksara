<?php

namespace App\Ai\Tools;

use App\Models\P5Project;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetCocurricularData implements Tool
{
    public function __construct(protected ?User $user = null) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Mendapatkan data kegiatan Kokurikuler (P5) yang sedang aktif di sekolah, termasuk tema dan profil lulusan yang ditargetkan.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        if (! $this->user) {
            return 'Error: User context missing.';
        }

        $args = $request->all();
        $fase = isset($args['fase']) ? (string) $args['fase'] : null;

        $query = P5Project::with('theme');

        if ($fase) {
            $query->where('fase', 'like', "%{$fase}%");
        }

        $projects = $query->limit(20)->get();

        if ($projects->isEmpty()) {
            return 'Data kokurikuler/P5 tidak ditemukan.';
        }

        $result = $projects->map(function ($p) {
            /** @var P5Project $p */
            return [
                'tema' => $p->theme ? $p->theme->name : 'N/A',
                'nama_projek' => $p->name,
                'fase' => $p->fase ?? 'N/A',
                'tujuan_akhir' => $p->target_description ?? 'N/A',
                'profil_lulusan' => $p->graduate_profile ?? [],
            ];
        });

        return json_encode([
            'total_data' => $projects->count(),
            'kegiatan_kokurikuler' => $result->toArray(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'fase' => $schema->string()->description('Opsional. Filter berdasarkan fase (contoh: "Fase D").'),
        ];
    }
}
