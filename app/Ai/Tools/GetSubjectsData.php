<?php

namespace App\Ai\Tools;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetSubjectsData implements Tool
{
    public function __construct(protected ?User $user = null) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Mencari informasi mata pelajaran, KKM, jam pelajaran (JP), dan apakah mapel tersebut bersifat umum atau kejuruan.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        if (! $this->user) {
            return 'Error: User context missing.';
        }

        $search = $request['search'] ?? null;
        $query = Subject::query()->with('level');

        if ($search) {
            $query->where('nama_mapel', 'like', "%{$search}%")
                ->orWhere('kode_mapel', 'like', "%{$search}%");
        }

        $subjects = $query->limit(50)->get();

        if ($subjects->isEmpty()) {
            return 'Mata pelajaran tidak ditemukan.';
        }

        $result = $subjects->map(function ($s) {
            /** @var Subject $s */
            return [
                'kode' => $s->kode_mapel ?? 'N/A',
                'nama_mapel' => $s->nama_mapel,
                'tingkat' => $s->level?->nama_tingkatan ?? 'Umum',
                'total_jp' => $s->total_jp.' Jam',
                'kkm' => $s->kkm,
                'kategori' => $s->is_umum ? 'Muatan Umum' : 'Muatan Khusus',
            ];
        });

        return json_encode([
            'total_mapel' => $subjects->count(),
            'mata_pelajaran' => $result->toArray(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Opsional. Cari nama atau kode mapel (contoh: Matematika, Fisika).'),
        ];
    }
}
