<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Subject;

$subjects = Subject::all();
foreach ($subjects as $s) {
    echo "ID: " . $s->id . " | Name: " . $s->nama_mapel . " | Code: " . $s->kode_mapel . " | JP: " . $s->total_jp . "\n";
}
