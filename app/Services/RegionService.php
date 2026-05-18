<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RegionService
{
    private static function fetchFromService(string $level, string $parent = '0'): array
    {
        $parent = trim((string)$parent);
        $cacheKey = "tateta_geo_v1_{$level}_{$parent}";
        
        return Cache::remember($cacheKey, 86400, function () use ($level, $parent) {
            $baseUrl = env('TATETA_GEO_URL', 'http://127.0.0.1:8001');
            
            // 1. Try TatetaGeo Local Microservice First with a short timeout of 2 seconds
            try {
                $endpoint = match($level) {
                    'provinsi' => 'v1/provinces',
                    'kabupaten' => 'v1/regencies?province_id=' . $parent,
                    'kecamatan' => 'v1/districts?regency_id=' . $parent,
                    'desa' => 'v1/villages?district_id=' . $parent,
                    default => null
                };
                
                if ($endpoint) {
                    $response = Http::timeout(2)
                        ->withToken(env('TATETA_GEO_TOKEN'))
                        ->get("{$baseUrl}/api/{$endpoint}");
                    if ($response->successful()) {
                        $data = $response->json();
                        $results = [];
                        foreach ($data as $item) {
                            $code = $item['id'] ?? null;
                            $name = $item['name'] ?? null;
                            if ($code && $name) {
                                $results[(string)$code] = strtoupper($name);
                            }
                        }
                        if (!empty($results)) return $results;
                    }
                }
            } catch (\Exception $e) {
                // Log and failover to secondary safety net (Emsifa GitHub Pages)
                Log::warning("TatetaGeo is offline/unreachable: " . $e->getMessage() . ". Falling back to Emsifa API.");
            }

            // 2. Secondary safety fallback: Fetch directly from Emsifa API (GitHub Pages)
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
    private static function getRawProvinces(): array { return self::fetchFromService('provinsi'); }
    private static function getRawRegencies($pId): array { return self::fetchFromService('kabupaten', $pId); }
    private static function getRawDistricts($rId): array { return self::fetchFromService('kecamatan', $rId); }
    private static function getRawVillages($dId): array { return self::fetchFromService('desa', $dId); }

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
        $baseUrl = env('TATETA_GEO_URL', 'http://127.0.0.1:8001');

        try {
            $response = Http::timeout(2)
                ->withToken(env('TATETA_GEO_TOKEN'))
                ->get("{$baseUrl}/api/v1/provinces/find", ['name' => $name]);
            if ($response->successful()) {
                $id = $response->json()['id'] ?? null;
                if ($id) return (string)$id;
            }
        } catch (\Exception $e) {
            // Offline fallback
        }

        $normSearch = self::normalize($name);
        foreach (self::getRawProvinces() as $id => $v) {
            if (self::normalize($v) === $normSearch) return (string)$id;
        }
        return null;
    }

    public static function findRegencyIdByName($provinceName, ?string $name): ?string
    {
        if (!$name) return null;
        $baseUrl = env('TATETA_GEO_URL', 'http://127.0.0.1:8001');

        try {
            $response = Http::timeout(2)
                ->withToken(env('TATETA_GEO_TOKEN'))
                ->get("{$baseUrl}/api/v1/regencies/find", [
                    'name' => $name,
                    'province_name' => $provinceName
                ]);
            if ($response->successful()) {
                $id = $response->json()['id'] ?? null;
                if ($id) return (string)$id;
            }
        } catch (\Exception $e) {
            // Offline fallback
        }

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
        $baseUrl = env('TATETA_GEO_URL', 'http://127.0.0.1:8001');

        try {
            $response = Http::timeout(2)
                ->withToken(env('TATETA_GEO_TOKEN'))
                ->get("{$baseUrl}/api/v1/districts/find", [
                    'name' => $name,
                    'regency_name' => $regencyName
                ]);
            if ($response->successful()) {
                $id = $response->json()['id'] ?? null;
                if ($id) return (string)$id;
            }
        } catch (\Exception $e) {
            // Offline fallback
        }

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
