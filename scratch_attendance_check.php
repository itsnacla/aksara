<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Attendance;
use Illuminate\Support\Carbon;

$attendances = Attendance::where('catatan', 'like', '%Otomatis%')->orderBy('tanggal')->get();
foreach($attendances as $a) {
    $date = Carbon::parse($a->tanggal);
    echo $a->tanggal . " (" . $date->isoFormat('dddd') . ")\n";
}
