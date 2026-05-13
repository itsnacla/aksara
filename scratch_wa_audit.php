<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\StudentParent;

$parents = StudentParent::whereNotNull('no_whatsapp')->where('no_whatsapp', '!=', '')->get();
echo "Total Parents with WA: " . $parents->count() . "\n";
foreach($parents->take(10) as $p) {
    echo "ID: " . $p->id . " | Name: " . $p->user->name . " | WA: " . $p->no_whatsapp . "\n";
}
