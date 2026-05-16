<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$level = 'kabupaten';
$parent = '11'; // Aceh
$periode = '2025_1.2025';

$url = 'https://sig.bps.go.id/rest-drop-down/getwilayah';

echo "Testing BPS API for Level: $level, Parent: $parent...\n";

$response = Http::withHeaders([
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
    'Accept' => 'application/json',
])->get($url, [
    'level' => $level,
    'parent' => $parent,
    'periode_merge' => $periode
]);

if ($response->successful()) {
    $data = $response->json();
    echo "SUCCESS! Received " . count($data) . " items.\n";
    if (count($data) > 0) {
        echo "First Item: " . $data[0]['kode'] . " => " . $data[0]['nama'] . "\n";
    }
} else {
    echo "FAILED! Status: " . $response->status() . "\n";
    echo "Response: " . $response->body() . "\n";
}
