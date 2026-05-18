<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Concurrency;

class RegionService
{
    private static function fetchFromBps(string $level, string $parent = '0'): array
    {
        $parent = trim((string)$parent);
        $cacheKey = "bps_region_v6_{$level}_{$parent}";
        
        return Cache::remember($cacheKey, 86400, function () use ($level, $parent) {
            // If BPS is flagged as blocked/down, bypass and go straight to Emsifa
            if (!Cache::has('bps_is_blocked')) {
                try {
                    // 1. Try BPS First with a short timeout of 2 seconds
                    $response = Http::timeout(2)->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'Accept' => 'application/json, text/plain, */*',
                        'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
                        'Referer' => 'https://sig.bps.go.id/',
                        'Origin' => 'https://sig.bps.go.id',
                    ])->get('https://sig.bps.go.id/rest-drop-down/getwilayah', [
                        'level' => $level,
                        'parent' => $parent,
                        'periode_merge' => '2025_1.2025'
                    ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        $results = [];
                        foreach ($data as $item) {
                            if (isset($item['kode']) && isset($item['nama'])) {
                                $results[(string)$item['kode']] = strtoupper($item['nama']);
                            }
                        }
                        if (!empty($results)) return $results;
                    }
                } catch (\Exception $e) {
                    // Flag BPS as blocked for 15 minutes to avoid hanging subsequent page requests
                    Cache::put('bps_is_blocked', true, 900);
                }
            }

            // 2. Fallback to Emsifa API (GitHub Pages)
            return self::fetchFromEmsifa($level, $parent);
        });
    }

    private static function fetchFromEmsifa(string $level, string $parent = '0'): array
    {
        $endpoints = [
            'provinsi' => 'provinces.json',
            'kabupaten' => "regencies/{$parent}.json",
            'kecamatan' => "districts/{$parent}.json",
            'desa' => "villages/{$parent}.json",
        ];

        if (!isset($endpoints[$level])) return [];

        try {
            $url = "https://www.emsifa.com/api-wilayah-indonesia/api/" . $endpoints[$level];
            $response = Http::timeout(10)->get($url);

            if (!$response->successful()) return [];

            $data = $response->json();
            $results = [];
            foreach ($data as $item) {
                // Emsifa uses 'id' instead of 'kode'
                $code = $item['id'] ?? $item['kode'] ?? null;
                $name = $item['name'] ?? $item['nama'] ?? null;
                if ($code && $name) {
                    $results[(string)$code] = strtoupper($name);
                }
            }
            return $results;
        } catch (\Exception $e) {
            return [];
        }
    }

    // INTERNAL: Get [ID => NAME]
    private static function getRawProvinces(): array { return self::fetchFromBps('provinsi'); }
    private static function getRawRegencies($pId): array { return self::fetchFromBps('kabupaten', $pId); }
    private static function getRawDistricts($rId): array { return self::fetchFromBps('kecamatan', $rId); }
    private static function getRawVillages($dId): array { return self::fetchFromBps('desa', $dId); }

    // PUBLIC: Get [NAME => NAME] for Filament Select
    public static function getProvinces(): array
    {
        $data = self::getRawProvinces();
        return array_combine(array_values($data), array_values($data));
    }

    public static function getRegencies($provinceName): array
    {
        if (!$provinceName) return [];
        $pId = self::findProvinceIdByName($provinceName);
        if (!$pId) return [];
        $data = self::getRawRegencies($pId);
        return array_combine(array_values($data), array_values($data));
    }

    public static function getDistricts($regencyName): array
    {
        if (!$regencyName) return [];
        $rId = self::findRegencyIdByName(null, $regencyName);
        if (!$rId) return [];
        $data = self::getRawDistricts($rId);
        return array_combine(array_values($data), array_values($data));
    }

    public static function getVillages($districtName, $regencyName = null): array
    {
        if (!$districtName) return [];
        $dId = self::findDistrictIdByName($regencyName, $districtName);
        if (!$dId) return [];
        $data = self::getRawVillages($dId);
        return array_combine(array_values($data), array_values($data));
    }

    public static function findProvinceIdByName(?string $name): ?string
    {
        if (!$name) return null;
        $normSearch = self::normalize($name);
        foreach (self::getRawProvinces() as $id => $v) {
            if (self::normalize($v) === $normSearch) return (string)$id;
        }
        return null;
    }

    public static function findRegencyIdByName($provinceName, ?string $name): ?string
    {
        if (!$name) return null;
        $normSearch = self::normalize($name);
        
        // 1. Try with province context first
        if ($provinceName) {
            $pId = is_numeric($provinceName) ? $provinceName : self::findProvinceIdByName($provinceName);
            if ($pId) {
                foreach (self::getRawRegencies($pId) as $id => $v) {
                    if (self::normalize($v) === $normSearch) return (string)$id;
                }
            }
        }
        
        // 2. Global search as fallback (expensive but safe)
        foreach (self::getRawProvinces() as $pId => $pName) {
            foreach (self::getRawRegencies($pId) as $id => $v) {
                if (self::normalize($v) === $normSearch) return (string)$id;
            }
        }
        return null;
    }

    public static function findDistrictIdByName($regencyName, ?string $name): ?string
    {
        if (!$name) return null;
        $normSearch = self::normalize($name);
        
        // 1. Try with regency context first
        if ($regencyName) {
            $rId = is_numeric($regencyName) ? $regencyName : self::findRegencyIdByName(null, $regencyName);
            if ($rId) {
                foreach (self::getRawDistricts($rId) as $id => $v) {
                    if (self::normalize($v) === $normSearch) return (string)$id;
                }
            }
        }

        // 2. Global search as fallback (if regency context failed or not provided)
        foreach (self::getRawProvinces() as $pId => $pName) {
            foreach (self::getRawRegencies($pId) as $rId => $rName) {
                foreach (self::getRawDistricts($rId) as $id => $v) {
                    if (self::normalize($v) === $normSearch) return (string)$id;
                }
            }
        }
        
        return null;
    }

    public static function findVillageIdByName($districtName, ?string $name): ?string
    {
        if (!$name) return null;
        $normSearch = self::normalize($name);
        $dId = is_numeric($districtName) ? $districtName : self::findDistrictIdByName(null, $districtName);
        if (!$dId) return null;

        foreach (self::getRawVillages($dId) as $id => $v) {
            if (self::normalize($v) === $normSearch) return (string)$id;
        }
        return null;
    }

    private static function normalize(?string $name): string
    {
        if (!$name) return '';
        // Remove prefixes more aggressively
        $name = preg_replace('/^(PROV\.|PROP\.|KAB\.|KOTA\.|KEC\.|KEL\.|PROVINSI|KABUPATEN|KOTA|KECAMATAN|DESA|KELURAHAN)\s+/i', '', trim($name));
        $name = str_replace(['DAERAH ISTIMEWA ', 'D.I. '], 'DI ', strtoupper($name));
        $name = preg_replace('/\s+/', '', $name); // Remove ALL spaces for comparison
        return strtoupper(preg_replace('/[^A-Z0-9]/', '', $name));
    }

    public static function getProvinceName($id) { return $id; }
    public static function getRegencyName($id, $pId) { return $id; }
    public static function getDistrictName($id, $rId) { return $id; }
    public static function getVillageName($id, $dId) { return $id; }
}
