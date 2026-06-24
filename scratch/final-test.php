<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\RegionService;

echo "--- FINAL DEBUGGING REGION SERVICE ---\n";

// 1. Test getProvinces
$provinces = RegionService::getProvinces();
echo "Total Provinces found: " . count($provinces) . "\n";
if (count($provinces) > 0) {
    echo "Sample Province: " . array_key_first($provinces) . " => " . reset($provinces) . "\n";
}

// 2. Test resolution from Name (Simulate what happens in the form)
$nameToSearch = "JAWA BARAT";
echo "\nSearching ID for: $nameToSearch ...\n";
$provinceId = RegionService::findProvinceIdByName($nameToSearch);
echo "Resolved ID: " . ($provinceId ?: 'NOT FOUND') . "\n";

if ($provinceId) {
    // 3. Test getRegencies using the resolved ID
    echo "\nFetching Regencies for ID $provinceId ...\n";
    $regencies = RegionService::getRegencies($provinceId);
    echo "Total Regencies found: " . count($regencies) . "\n";
    if (count($regencies) > 0) {
        echo "Sample Regency: " . array_key_first($regencies) . " => " . reset($regencies) . "\n";
    } else {
        echo "FAIL: No regencies found for $provinceId\n";
    }
}
