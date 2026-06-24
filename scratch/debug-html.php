<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$url = 'https://referensi.data.kemendikdasmen.go.id/pendidikan/dikdas/020000/1';
echo "Fetching: $url ...\n";

$response = Http::withHeaders([
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
])->get($url);

if ($response->successful()) {
    $html = $response->body();
    echo "SUCCESS! Received " . strlen($html) . " bytes.\n";
    
    // Check if regex matches
    preg_match_all('/href=[\'"][^\'"]+\/pendidikan\/[^\/]+\/([0-9]+)\/([0-9])[\'"]\s*>\s*(.*?)\s*<\/a>/is', $html, $matches);
    
    echo "Matches found: " . count($matches[0]) . "\n";
    if (count($matches[0]) > 0) {
        echo "First match: " . $matches[1][0] . " => " . $matches[3][0] . "\n";
    } else {
        echo "REGEX FAILED. Showing first 1000 chars of HTML:\n";
        echo substr($html, 0, 1000) . "\n";
        
        // Find any links with /pendidikan/
        echo "\nLooking for any /pendidikan/ links manually...\n";
        if (preg_match_all('/href=[\'"][^\'"]*\/pendidikan\/[^\'"]*[\'"]/i', $html, $links)) {
            print_r(array_slice($links[0], 0, 5));
        }
    }
} else {
    echo "FAILED! Status: " . $response->status() . "\n";
}
