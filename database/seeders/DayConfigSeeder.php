<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DayConfig;
use App\Models\AcademicYear;
use App\Models\Subject;
use App\Models\TimeSlot;
use App\Models\Level;

class DayConfigSeeder extends Seeder
{
    public function run(): void
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        if (!$activeYear) return;

        $upacaraMapel = Subject::where('nama_mapel', 'Upacara')->first();
        $jam1 = TimeSlot::where('urutan', 1)->first();
        $istirahat = TimeSlot::where('urutan', 5)->first();
        $jam5 = TimeSlot::where('urutan', 6)->first(); // Jam Kelima
        $jam6 = TimeSlot::where('urutan', 7)->first(); // Jam Keenam
        $jam8 = TimeSlot::where('urutan', 9)->first(); // Jam Kedelapan
        
        $levels12 = Level::whereIn('nama_tingkatan', ['Kelas 1', 'Kelas 2'])->pluck('id')->toArray();
        $levels16 = Level::whereIn('nama_tingkatan', ['Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4', 'Kelas 5', 'Kelas 6'])->pluck('id')->toArray();
        $levels36 = Level::whereIn('nama_tingkatan', ['Kelas 3', 'Kelas 4', 'Kelas 5', 'Kelas 6'])->pluck('id')->toArray();
        $allLevels = Level::pluck('id')->toArray();

        // 1. SENIN - KAMIS
        foreach (['Senin', 'Selasa', 'Rabu', 'Kamis'] as $day) {
            // Aturan Kelas 1-2
            DayConfig::create([
                'academic_year_id' => $activeYear->id,
                'day' => $day,
                'level_ids' => $levels12,
                'max_time_slot_id' => $jam6?->id,
                'mandatory_subject_id' => ($day === 'Senin') ? $upacaraMapel?->id : null,
                'mandatory_time_slot_id' => ($day === 'Senin') ? $jam1?->id : null,
            ]);

            // Aturan Kelas 3-6
            DayConfig::create([
                'academic_year_id' => $activeYear->id,
                'day' => $day,
                'level_ids' => $levels36,
                'max_time_slot_id' => $jam8?->id,
                'mandatory_subject_id' => ($day === 'Senin') ? $upacaraMapel?->id : null,
                'mandatory_time_slot_id' => ($day === 'Senin') ? $jam1?->id : null,
            ]);
        }

        // 2. JUMAT
        DayConfig::create([
            'academic_year_id' => $activeYear->id,
            'day' => 'Jumat',
            'level_ids' => $levels16,
            'max_time_slot_id' => $istirahat?->id,
        ]);

        // 3. SABTU
        DayConfig::create([
            'academic_year_id' => $activeYear->id,
            'day' => 'Sabtu',
            'level_ids' => $allLevels,
            'max_time_slot_id' => $jam5?->id,
        ]);
    }
}
