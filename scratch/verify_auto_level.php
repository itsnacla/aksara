<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Subject;
use App\Models\LearningObjective;

$subject = Subject::where('nama_mapel', 'Matematika')->first();

if (!$subject) {
    echo "Gagal: Subjek Matematika tidak ditemukan!" . PHP_EOL;
    exit(1);
}

echo "Subjek: {$subject->nama_mapel} | ID: {$subject->id} | Level ID: {$subject->level_id}" . PHP_EOL;

// 1. Create a TP without level_id
$lo = new LearningObjective();
$lo->subject_id = $subject->id;
$lo->code = "TP 9.9"; // Dummy code
$lo->description = "Menjelaskan operasi kalkulus pecahan secara sederhana.";
$lo->is_active = true;

// Save! The model hook should automatically set level_id from the subject!
$lo->save();

echo "Berhasil menyimpan TP baru!" . PHP_EOL;
echo "Tersimpan -> Subject ID: {$lo->subject_id} | Level ID: {$lo->level_id}" . PHP_EOL;

if ($lo->level_id === $subject->level_id) {
    echo "SUKSES: level_id secara otomatis terisi dan sama dengan subject->level_id!" . PHP_EOL;
} else {
    echo "ERROR: level_id tidak terisi secara otomatis!" . PHP_EOL;
}

// Clean up
$lo->delete();
echo "Pembersihan: TP berhasil dihapus." . PHP_EOL;
