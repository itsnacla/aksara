<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use App\Models\DayConfig;
use App\Models\Subject;
use App\Models\TimeSlot;
use Illuminate\Contracts\Console\Kernel;

echo "--- Day Configs ---\n";
$configs = DayConfig::all();
foreach ($configs as $c) {
    $subject = Subject::find($c->mandatory_subject_id);
    $slot = TimeSlot::find($c->mandatory_time_slot_id);
    echo "Day: {$c->day} | Level IDs: ".json_encode($c->level_ids)."\n";
    echo '  Mandatory Subject: '.($subject?->nama_mapel ?? 'None')." (ID: {$c->mandatory_subject_id})\n";
    echo '  Mandatory Slot: '.($slot?->nama_jam ?? 'None')." (ID: {$c->mandatory_time_slot_id})\n";
}

echo "\n--- Subjects ---\n";
$upacara = Subject::where('nama_mapel', 'Upacara')->first();
echo 'Upacara ID: '.($upacara?->id ?? 'Not Found')."\n";
echo 'Upacara is_umum: '.($upacara?->is_umum ? 'Yes' : 'No')."\n";
