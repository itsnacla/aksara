<?php

namespace App\Services;

class RegionService
{
    private static array $cache = [];

    private static function getJsonData(string $filename): array
    {
        if (isset(self::$cache[$filename])) {
            return self::$cache[$filename];
        }

        $path = storage_path("app/geo/{$filename}");
        if (!file_exists($path)) {
            return self::$cache[$filename] = [];
        }

        $content = file_get_contents($path);
        return self::$cache[$filename] = json_decode($content, true) ?? [];
    }

    // PUBLIC: Get [NAME => NAME] for Filament Select
    public static function getProvinces(): array
    {
        $data = self::getJsonData('provinces.json');
        $results = [];
        foreach ($data as $item) {
            if (isset($item['name'])) {
                $results[$item['name']] = $item['name'];
            }
        }
        return $results;
    }

    public static function getRegencies($provinceName): array
    {
        if (! $provinceName) {
            return [];
        }
        $pId = self::findProvinceIdByName($provinceName);
        if (! $pId) {
            return [];
        }

        $data = self::getJsonData('regencies.json');
        $results = [];
        foreach ($data as $item) {
            if (($item['province_id'] ?? null) == $pId && isset($item['name'])) {
                $results[$item['name']] = $item['name'];
            }
        }
        return $results;
    }

    public static function getDistricts($regencyName): array
    {
        if (! $regencyName) {
            return [];
        }
        $rId = self::findRegencyIdByName(null, $regencyName);
        if (! $rId) {
            return [];
        }

        $data = self::getJsonData('districts.json');
        $results = [];
        foreach ($data as $item) {
            if (($item['regency_id'] ?? null) == $rId && isset($item['name'])) {
                $results[$item['name']] = $item['name'];
            }
        }
        return $results;
    }

    public static function getVillages($districtName, $regencyName = null): array
    {
        if (! $districtName) {
            return [];
        }
        $dId = self::findDistrictIdByName($regencyName, $districtName);
        if (! $dId) {
            return [];
        }

        $provinceId = substr((string)$dId, 0, 2);
        $data = self::getJsonData("villages/{$provinceId}.json");
        $results = [];
        foreach ($data as $item) {
            if (($item['district_id'] ?? null) == $dId && isset($item['name'])) {
                $results[$item['name']] = $item['name'];
            }
        }
        return $results;
    }

    public static function findProvinceIdByName(?string $name): ?string
    {
        if (! $name) {
            return null;
        }

        $normSearch = self::normalize($name);
        $data = self::getJsonData('provinces.json');
        foreach ($data as $item) {
            if (isset($item['name'], $item['id']) && self::normalize($item['name']) === $normSearch) {
                return (string) $item['id'];
            }
        }

        return null;
    }

    public static function findRegencyIdByName($provinceName, ?string $name): ?string
    {
        if (! $name) {
            return null;
        }

        $normSearch = self::normalize($name);
        $data = self::getJsonData('regencies.json');

        // 1. Try with province context first
        if ($provinceName) {
            $pId = is_numeric($provinceName) ? $provinceName : self::findProvinceIdByName($provinceName);
            if ($pId) {
                foreach ($data as $item) {
                    if (($item['province_id'] ?? null) == $pId && isset($item['name'], $item['id']) && self::normalize($item['name']) === $normSearch) {
                        return (string) $item['id'];
                    }
                }
            }
        }

        // 2. Global search as fallback
        foreach ($data as $item) {
            if (isset($item['name'], $item['id']) && self::normalize($item['name']) === $normSearch) {
                return (string) $item['id'];
            }
        }

        return null;
    }

    public static function findDistrictIdByName($regencyName, ?string $name): ?string
    {
        if (! $name) {
            return null;
        }

        $normSearch = self::normalize($name);
        $data = self::getJsonData('districts.json');

        // 1. Try with regency context first
        if ($regencyName) {
            $rId = is_numeric($regencyName) ? $regencyName : self::findRegencyIdByName(null, $regencyName);
            if ($rId) {
                foreach ($data as $item) {
                    if (($item['regency_id'] ?? null) == $rId && isset($item['name'], $item['id']) && self::normalize($item['name']) === $normSearch) {
                        return (string) $item['id'];
                    }
                }
            }
        }

        // 2. Global search as fallback
        foreach ($data as $item) {
            if (isset($item['name'], $item['id']) && self::normalize($item['name']) === $normSearch) {
                return (string) $item['id'];
            }
        }

        return null;
    }

    public static function findVillageIdByName($districtName, ?string $name): ?string
    {
        if (! $name) {
            return null;
        }

        $normSearch = self::normalize($name);
        $dId = is_numeric($districtName) ? $districtName : self::findDistrictIdByName(null, $districtName);
        if (! $dId) {
            return null;
        }

        $provinceId = substr((string)$dId, 0, 2);
        $data = self::getJsonData("villages/{$provinceId}.json");

        foreach ($data as $item) {
            if (($item['district_id'] ?? null) == $dId && isset($item['name'], $item['id']) && self::normalize($item['name']) === $normSearch) {
                return (string) $item['id'];
            }
        }

        return null;
    }

    private static function normalize(?string $name): string
    {
        if (! $name) {
            return '';
        }
        $name = preg_replace('/^(PROV\.|PROP\.|KAB\.|KOTA\.|KEC\.|KEL\.|PROVINSI|KABUPATEN|KOTA|KECAMATAN|DESA|KELURAHAN)\s+/i', '', trim($name));
        $name = str_replace(['DAERAH ISTIMEWA ', 'D.I. '], 'DI ', strtoupper($name));
        $name = preg_replace('/\s+/', '', $name); // Remove ALL spaces for comparison

        return strtoupper(preg_replace('/[^A-Z0-9]/', '', $name));
    }

    public static function getProvinceName($id)
    {
        return $id;
    }

    public static function getRegencyName($id, $pId)
    {
        return $id;
    }

    public static function getDistrictName($id, $rId)
    {
        return $id;
    }

    public static function getVillageName($id, $dId)
    {
        return $id;
    }
}
