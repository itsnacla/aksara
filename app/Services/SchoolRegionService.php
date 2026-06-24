<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SchoolRegionService
{
    public static function getProvinces(): array
    {
        return RegionService::getProvinces();
    }

    public static function getRegencies($provinceName): array
    {
        return RegionService::getRegencies($provinceName);
    }

    public static function getDistricts($regencyName): array
    {
        return RegionService::getDistricts($regencyName);
    }

    public static function getVillages($districtName): array
    {
        return RegionService::getVillages($districtName);
    }

    // --- FINDER METHODS ---

    public static function findProvinceIdByName($name): ?string
    {
        return self::findInArray($name, self::getProvinces());
    }

    public static function findRegencyIdByName($provinceName, $regencyName): ?string
    {
        return self::findInArray($regencyName, self::getRegencies($provinceName));
    }

    public static function findDistrictIdByName($regencyName, $districtName): ?string
    {
        return self::findInArray($districtName, self::getDistricts($regencyName));
    }

    public static function findVillageIdByName($districtName, $villageName): ?string
    {
        return self::findInArray($villageName, self::getVillages($districtName));
    }

    /**
     * Deep Clean name for matching.
     */
    public static function standardize($name): string
    {
        if (!$name) return '';
        
        $name = strtoupper(trim($name));
        
        // Remove common prefixes and punctuation
        $patterns = [
            '/^(PROV\.|PROP\.|PROVINSI|PROP)\s+/i',
            '/^(KAB\.|KABUPATEN|KOTA)\s+/i',
            '/^(KEC\.|KECAMATAN)\s+/i',
            '/^(DESA|KEL\.|KELURAHAN)\s+/i',
            '/\./', // Remove dots like in "KAB."
        ];
        
        $name = preg_replace($patterns, '', $name);
        
        return trim($name);
    }

    private static function findInArray($search, $array): ?string
    {
        if (!$search || empty($array)) return null;
        
        $searchStandard = self::standardize($search);
        
        foreach ($array as $code => $name) {
            // Compare standardized versions
            if (self::standardize($name) === $searchStandard) {
                return (string) $code; // Return the ORIGINAL key
            }
        }
        
        return null;
    }
}
