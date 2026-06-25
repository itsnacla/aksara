<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Services\RegionService;
use Illuminate\Contracts\Console\Kernel;

$cityId = '3525'; // Gresik
$districts = RegionService::getDistricts($cityId);

foreach ($districts as $id => $name) {
    $villages = RegionService::getVillages($id);
    foreach ($villages as $vid => $vname) {
        if (str_contains(strtoupper($vname), 'SEMBUNG')) {
            echo "FOUND: $vname in District $name (ID: $id)\n";
        }
    }
}
