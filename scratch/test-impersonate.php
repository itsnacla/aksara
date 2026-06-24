<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Http\Controllers\Portal\ImpersonateController;
use Illuminate\Http\Request;

echo "--- RUNNING PROGRAMMATIC IMPERSONATION TEST ---\n";

// 1. Get users
$admin = User::find(1); // Super Admin
$targetUser = User::whereHas('roles', function($q) {
    $q->where('name', 'siswa');
})->first(); // Find a student

if (!$targetUser) {
    echo "Error: No student user found in database!\n";
    exit(1);
}

echo "Original User: {$admin->name} (Role: {$admin->roles->first()?->name})\n";
echo "Target User to Impersonate: {$targetUser->name} (Role: {$targetUser->roles->first()?->name})\n";

// 2. Log in as Admin first
auth()->login($admin);
echo "Logged in as Admin: " . (auth()->check() && auth()->user()->id === $admin->id ? "YES" : "NO") . "\n";

// 3. Initiate Impersonate
echo "Initiating 'Login As'...\n";
$controller = new ImpersonateController();
$request = Request::create('/impersonate/login/' . $targetUser->id, 'POST');

// Call controller login method
$response = $controller->login($request, $targetUser);

echo "Session impersonator_id: " . session('impersonator_id') . "\n";
echo "Current active authenticated user ID: " . auth()->id() . " (" . auth()->user()->name . ")\n";
echo "Is impersonating successful? " . (auth()->id() === $targetUser->id && session('impersonator_id') === $admin->id ? "SUCCESS ✅" : "FAILED ❌") . "\n";

// 4. Return back to admin
echo "Initiating 'Return to Admin'...\n";
$responseLogout = $controller->logout();

echo "Session impersonator_id after logout: " . (session()->has('impersonator_id') ? session('impersonator_id') : "CLEARED") . "\n";
echo "Current active authenticated user ID: " . auth()->id() . " (" . auth()->user()->name . ")\n";
echo "Is return successful? " . (auth()->id() === $admin->id && !session()->has('impersonator_id') ? "SUCCESS ✅" : "FAILED ❌") . "\n";

echo "--- TEST COMPLETE ---\n";
