<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use App\Models\Schedule;
use App\Models\StudyGroup;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Contracts\Console\Kernel;

$rombel = StudyGroup::first();
echo 'Debugging for Rombel: '.$rombel->nama_rombel.' (ID: '.$rombel->id.")\n";
echo 'Wali Kelas ID: '.($rombel->walikelas_id ?? 'NULL')."\n";

$subjects = Subject::where(function ($q) use ($rombel) {
    $q->whereHas('levels', fn ($sq) => $sq->where('levels.id', $rombel->level_id))
        ->orDoesntHave('levels');
})->get();

echo 'Found '.$subjects->count()." potential subjects.\n";

foreach ($subjects as $s) {
    $teacherId = $s->is_umum
        ? $rombel->walikelas_id
        : Teacher::whereHas('subjects', fn ($q) => $q->where('subjects.id', $s->id))
            ->where('status', 'aktif')->first()?->id;

    $usedJp = Schedule::where('study_group_id', $rombel->id)
        ->where('subject_id', $s->id)->count(); // Simplified for debug

    $remaining = $s->total_jp - $usedJp;

    echo "- Subject: {$s->nama_mapel} | Teacher: ".($teacherId ?? 'MISSING')." | Remaining: {$remaining}\n";
}
