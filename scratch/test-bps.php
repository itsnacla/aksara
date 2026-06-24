<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\RegionService;

echo "Testing RegionService which now has a smart fallback and block caching...\n";

$start = microtime(true);
$provinces = RegionService::getProvinces();
$end = microtime(true);

echo "SUCCESS! Fetched " . count($provinces) . " provinces.\n";
if (!empty($provinces)) {
    echo "First Province: " . reset($provinces) . "\n";
}
echo "Time Taken: " . number_format($end - $start, 4) . " seconds.\n";

$start2 = microtime(true);
$provinces2 = RegionService::getProvinces();
$end2 = microtime(true);
echo "Second Fetch (Cached): Time Taken: " . number_format($end2 - $start2, 4) . " seconds.\n";
