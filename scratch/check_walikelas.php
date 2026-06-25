<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use App\Models\StudyGroup;
use Illuminate\Contracts\Console\Kernel;

$groups = StudyGroup::all();
$walikelasIds = $groups->pluck('walikelas_id')->toArray();
$uniqueIds = array_unique($walikelasIds);

echo 'Total Groups: '.$groups->count()."\n";
echo 'Unique Wali Kelas: '.count($uniqueIds)."\n";

foreach ($groups as $g) {
    echo "- Group: {$g->nama_rombel} | Wali Kelas ID: {$g->walikelas_id}\n";
}
