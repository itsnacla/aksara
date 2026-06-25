<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KemendikbudService
{
    private static $refUrl = 'https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn';

    /**
     * Fetch basic school data from Kemendikbud reference site by NPSN.
     * Simple, stable, and straightforward.
     */
    public static function fetchByNpsn(string $npsn): array
    {
        $url = self::$refUrl."/{$npsn}";

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9,id;q=0.8',
                'Cache-Control' => 'no-cache',
                'Pragma' => 'no-cache',
            ])
                ->withoutVerifying()
                ->timeout(30)
                ->get($url);

            if (! $response->successful()) {
                return ['success' => false, 'message' => 'Gagal terhubung ke server Kemendikbud.'];
            }

            $html = $response->body();

            $data = [
                'success' => true,
                'name' => self::extract($html, '/Nama<\/td>\s*<td>:<\/td>\s*<td>(.*?)<\/td>/is'),
                'npsn' => $npsn,
                'school_level' => self::extract($html, '/Bentuk Pendidikan<\/td>\s*<td>:<\/td>\s*<td>(.*?)<\/td>/is'),
                'address' => self::extract($html, '/Alamat<\/td>\s*<td>:<\/td>\s*<td>(.*?)<\/td>/is'),
                'village' => self::extract($html, '/Desa\/Kelurahan<\/td>\s*<td>:<\/td>\s*<td>(.*?)<\/td>/is'),
                'district' => self::extract($html, '/Kecamatan\/Kota \(LN\)<\/td>\s*<td>:<\/td>\s*<td>(.*?)<\/td>/is'),
                'city' => self::extract($html, '/Kab\.-Kota\/Negara \(LN\)<\/td>\s*<td>:<\/td>\s*<td>(.*?)<\/td>/is'),
                'province' => self::extract($html, '/Propinsi\/Luar Negeri \(LN\)<\/td>\s*<td>:<\/td>\s*<td>(.*?)<\/td>/is'),
                'email' => self::extract($html, '/Email<\/td>\s*<td>:<\/td>\s*<td>(.*?)<\/td>/is'),
                'website' => self::extract($html, '/Website<\/td>\s*<td>:<\/td>\s*<td>(.*?)<\/td>/is'),
            ];

            return self::normalizeData($data);

        } catch (\Exception $e) {
            Log::error('Kemendikbud Fetch Error: '.$e->getMessage());

            return ['success' => false, 'message' => 'Terjadi kesalahan: '.$e->getMessage()];
        }
    }

    private static function normalizeData(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Bersihkan HTML tag dan decode entities
                $cleaned = trim(strip_tags(html_entity_decode($value)));

                // Gunakan logic standarisasi yang sama agar matching
                if (in_array($key, ['village', 'district', 'city', 'province'])) {
                    $cleaned = SchoolRegionService::standardize($cleaned);
                }

                $data[$key] = ($cleaned === '-' || empty($cleaned)) ? null : trim($cleaned);
            }
        }

        return $data;
    }

    private static function extract(string $html, string $pattern): ?string
    {
        if (preg_match($pattern, $html, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
