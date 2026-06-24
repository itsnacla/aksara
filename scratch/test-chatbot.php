<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Ai\Agents\AksaraAssistant;
use App\Models\User;

$user = User::find(1); // Super Admin
echo "Simulating Chatbot request as: " . $user->name . "\n";

$agent = new AksaraAssistant($user);
$userMessage = "Cari data siswa bernama Nina Wijaya";

echo "Prompt: $userMessage\n";
echo "Generating AI Response (this may call GetStudentDetails tool)...\n";

$start = microtime(true);
$response = $agent->forUser($user)->prompt($userMessage);
$end = microtime(true);

echo "\n--- AI RESPONSE ---\n";
echo (string) $response;
echo "\n--------------------\n";
echo "Time Taken: " . number_format($end - $start, 4) . " seconds.\n";
