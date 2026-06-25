<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\Subject;
use Illuminate\Contracts\Console\Kernel;

$subjects = Subject::all();
foreach ($subjects as $s) {
    echo 'ID: '.$s->id.' | Name: '.$s->nama_mapel.' | Code: '.$s->kode_mapel.' | JP: '.$s->total_jp."\n";
}
