<?php

namespace App\Ai\Tools;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetTeacherDirectory implements Tool
{
    public function __construct(protected ?User $user = null) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Mencari direktori guru, termasuk nama lengkap, NIP, kontak, dan status wali kelas/kepala sekolah.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        if (!$this->user) {
            return 'Error: User context missing.';
        }

        $search = $request['search'] ?? null;
        $roleName = $this->user->roles->first()?->name ?? 'siswa';

        $query = Teacher::query()->with('user');

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhere('nip', 'like', "%{$search}%");
        }

        $teachers = $query->limit(50)->get();

        if ($teachers->isEmpty()) {
            return 'Data guru tidak ditemukan.';
        }

        $result = $teachers->map(function ($t) use ($roleName) {
            /** @var Teacher $t */
            $namaLengkap = trim(($t->gelar_depan ? $t->gelar_depan . ' ' : '') . $t->user->name . ($t->gelar_belakang ? ', ' . $t->gelar_belakang : ''));
            
            $data = [
                'nama' => $namaLengkap,
                'nip' => $t->nip ?? 'N/A',
                'kode_guru' => $t->kode_guru ?? 'N/A',
                'jabatan' => $t->is_kepalasekolah ? 'Kepala Sekolah' : ($t->is_walikelas ? 'Wali Kelas' : 'Guru Mapel'),
                'status' => $t->status,
            ];

            // Tampilkan kontak hanya untuk Admin, Staff, atau Guru lain
            if (str_contains($roleName, 'admin') || str_contains($roleName, 'staff') || str_contains($roleName, 'guru')) {
                $data['kontak_wa'] = $t->no_whatsapp ?? 'N/A';
                $data['email'] = $t->user->email;
            }

            return $data;
        });

        return json_encode([
            'total_ditemukan' => $teachers->count(),
            'guru' => $result->toArray(),
            'catatan' => str_contains($roleName, 'siswa') || str_contains($roleName, 'orang_tua') ? 'Nomor kontak pribadi disembunyikan untuk alasan privasi.' : '',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Opsional. Cari nama atau NIP guru.'),
        ];
    }
}
