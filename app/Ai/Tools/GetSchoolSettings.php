<?php

namespace App\Ai\Tools;

use App\Models\SchoolSetting;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetSchoolSettings implements Tool
{
    public function __construct(protected ?User $user = null) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Gunakan tool ini untuk mendapatkan informasi umum sekolah seperti nama, NPSN, alamat, visi misi, kontak, dan pengaturan lainnya.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        if (!$this->user) {
            return 'Error: User context missing.';
        }

        $setting = SchoolSetting::first();

        if (!$setting) {
            return 'Data sekolah belum diatur di sistem.';
        }

        return json_encode([
            'nama_sekolah' => $setting->name,
            'npsn' => $setting->npsn,
            'motto_atau_visi_misi' => $setting->motto,
            'alamat' => $setting->address,
            'desa' => $setting->village,
            'kecamatan' => $setting->district,
            'kota' => $setting->city,
            'provinsi' => $setting->province,
            'telepon' => $setting->phone,
            'email' => $setting->email,
            'website' => $setting->website,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return []; // Tidak perlu parameter, langsung return data sekolah
    }
}
