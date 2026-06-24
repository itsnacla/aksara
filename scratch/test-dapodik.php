<?php

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\Http;

// Inisialisasi Laravel context
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Dapodik API from Server...\n";

$url = "https://dapo.kemendikdasmen.go.id/rekap/progresSP?id_level_wilayah=3&kode_wilayah=052510&semester_id=20252";

try {
    $response = Http::withHeaders([
        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept' => 'application/json, text/javascript, */*; q=0.01',
        'Referer' => 'https://dapo.kemendikdasmen.go.id/sp',
        'X-Requested-With' => 'XMLHttpRequest',
    ])
    ->withoutVerifying()
    ->get($url);

    echo "Status Code: " . $response->status() . "\n";
    echo "Response Body (First 200 chars): " . substr($response->body(), 0, 200) . "...\n";
    
    if ($response->status() === 404) {
        echo "\n[DIAGNOSIS]: Server memberikan 404. Ini biasanya karena proteksi Firewall (WAF) yang memblokir IP server Anda atau butuh Cookie Session.\n";
    }

} catch (\Exception $e) {
    echo "Connection Error: " . $e->getMessage() . "\n";
}
