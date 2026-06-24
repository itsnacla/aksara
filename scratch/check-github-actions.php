<?php

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/repos/itsnacla/aksara/commits/90d18a1/check-runs');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/vnd.github.v3+json'
]);

$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Error: ' . curl_error($ch) . PHP_EOL;
} else {
    $data = json_decode($response, true);
    if (isset($data['check_runs'])) {
        foreach ($data['check_runs'] as $run) {
            echo "Name: " . $run['name'] . PHP_EOL;
            echo "Status: " . $run['status'] . PHP_EOL;
            echo "Conclusion: " . ($run['conclusion'] ?? 'in_progress') . PHP_EOL;
            echo "URL: " . $run['html_url'] . PHP_EOL;
            echo "-----------------------------------" . PHP_EOL;
        }
    } else {
        echo "No check runs found or API limit exceeded: " . $response . PHP_EOL;
    }
}
curl_close($ch);
