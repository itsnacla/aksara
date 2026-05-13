<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RegionService
{
    protected static $baseUrl = 'https://emsifa.github.io/api-wilayah-indonesia/api';

    public static function getProvinces(): array
    {
        return Cache::remember('provinces', 86400, function () {
            $response = Http::get(self::$baseUrl . '/provinces.json');
            if ($response->successful()) {
                return collect($response->json())->pluck('name', 'id')->toArray();
            }
            return [];
        });
    }

    public static function getRegencies($provinceId): array
    {
        if (!$provinceId) return [];
        return Cache::remember("regencies_{$provinceId}", 86400, function () use ($provinceId) {
            $response = Http::get(self::$baseUrl . "/regencies/{$provinceId}.json");
            if ($response->successful()) {
                return collect($response->json())->pluck('name', 'id')->toArray();
            }
            return [];
        });
    }

    public static function getDistricts($regencyId): array
    {
        if (!$regencyId) return [];
        return Cache::remember("districts_{$regencyId}", 86400, function () use ($regencyId) {
            $response = Http::get(self::$baseUrl . "/districts/{$regencyId}.json");
            if ($response->successful()) {
                return collect($response->json())->pluck('name', 'id')->toArray();
            }
            return [];
        });
    }

    public static function getVillages($districtId): array
    {
        if (!$districtId) return [];
        return Cache::remember("villages_{$districtId}", 86400, function () use ($districtId) {
            $response = Http::get(self::$baseUrl . "/villages/{$districtId}.json");
            if ($response->successful()) {
                return collect($response->json())->pluck('name', 'id')->toArray();
            }
            return [];
        });
    }

    public static function getProvinceName($id): ?string
    {
        return self::getProvinces()[$id] ?? $id;
    }

    public static function getRegencyName($id, $provinceId): ?string
    {
        return self::getRegencies($provinceId)[$id] ?? $id;
    }

    public static function getDistrictName($id, $regencyId): ?string
    {
        return self::getDistricts($regencyId)[$id] ?? $id;
    }

    public static function getVillageName($id, $districtId): ?string
    {
        return self::getVillages($districtId)[$id] ?? $id;
    }
}
