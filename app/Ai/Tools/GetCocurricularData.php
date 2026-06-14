<?php

namespace App\Ai\Tools;

use App\Models\Cocurricular;
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
        return 'Mendapatkan data program kokurikuler yang tersedia (seperti kegiatan pendalaman materi, proyek luar kelas non-P5).';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        if (!$this->user) {
            return 'Error: User context missing.';
        }

        $args = $request->all();
        $fase = isset($args['fase']) ? (string) $args['fase'] : null;

        $query = Cocurricular::query();

        if ($fase) {
            $query->where('fase', 'like', "%{$fase}%");
        }

        $cocurriculars = $query->limit(20)->get();

        if ($cocurriculars->isEmpty()) {
            return 'Data kokurikuler tidak ditemukan.';
        }

        $result = $cocurriculars->map(function ($c) {
            /** @var Cocurricular $c */
            return [
                'tema' => $c->tema,
                'nama_projek' => $c->nama_projek,
                'fase' => $c->fase,
                'deskripsi' => $c->deskripsi ?? 'N/A',
                'tahun_ajaran' => $c->tahun_ajaran ?? 'N/A',
            ];
        });

        return json_encode([
            'total_data' => $cocurriculars->count(),
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
