<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use App\Models\DayConfig;
use App\Models\Level;
use Illuminate\Contracts\Console\Kernel;

$level = Level::first();
echo 'Testing for Level: '.$level->nama_tingkatan.' (ID: '.$level->id.")\n";

$configs = DayConfig::whereJsonContains('level_ids', (int) $level->id)->get();
echo 'Found '.$configs->count()." configs using (int)\n";

$configsStr = DayConfig::whereJsonContains('level_ids', (string) $level->id)->get();
echo 'Found '.$configsStr->count()." configs using (string)\n";

foreach ($configs as $c) {
    echo '- Day: '.$c->day.' | Mandatory: '.($c->mandatory_subject_id ?? 'None')."\n";
}
