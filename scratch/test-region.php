<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Services\RegionService;
use Illuminate\Contracts\Console\Kernel;

$npsn = '60719240';
$province = 'JAWA TIMUR';
$city = 'KAB. GRESIK';
$district = 'KEC. WRINGIN ANOM';
$village = 'SEMBUNG';

echo "--- TESTING REGION MAPPING ---\n";

$provinceId = RegionService::findProvinceIdByName($province);
echo "Province: $province -> ID: ".($provinceId ?: 'NOT FOUND')."\n";

if ($provinceId) {
    $cityId = RegionService::findRegencyIdByName($provinceId, $city);
    echo "City: $city -> ID: ".($cityId ?: 'NOT FOUND')."\n";

    if ($cityId) {
        $districtId = RegionService::findDistrictIdByName($cityId, $district);
        echo "District: $district -> ID: ".($districtId ?: 'NOT FOUND')."\n";

        if ($districtId) {
            $villageId = RegionService::findVillageIdByName($districtId, $village);
            echo "Village: $village -> ID: ".($villageId ?: 'NOT FOUND')."\n";

            if (! $villageId) {
                echo "Villages available in District ID $districtId:\n";
                print_r(RegionService::getVillages($districtId));
            }
        } else {
            echo "Districts available in City ID $cityId:\n";
            print_r(RegionService::getDistricts($cityId));
        }
    }
}
