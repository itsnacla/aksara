<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\TimeSlot;
use Illuminate\Database\Seeder;

class TimeSlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function up(): void
    {
        $slots = [
            ['nama_jam' => 'Jam Pertama', 'waktu_mulai' => '07:00', 'waktu_selesai' => '07:35', 'is_istirahat' => false, 'urutan' => 1],
            ['nama_jam' => 'Jam Kedua', 'waktu_mulai' => '07:35', 'waktu_selesai' => '08:10', 'is_istirahat' => false, 'urutan' => 2],
            ['nama_jam' => 'Jam Ketiga', 'waktu_mulai' => '08:10', 'waktu_selesai' => '08:45', 'is_istirahat' => false, 'urutan' => 3],
            ['nama_jam' => 'Jam Keempat', 'waktu_mulai' => '08:45', 'waktu_selesai' => '09:20', 'is_istirahat' => false, 'urutan' => 4],
            ['nama_jam' => 'Istirahat', 'waktu_mulai' => '09:20', 'waktu_selesai' => '09:50', 'is_istirahat' => true, 'urutan' => 5],
            ['nama_jam' => 'Jam Kelima', 'waktu_mulai' => '09:50', 'waktu_selesai' => '10:20', 'is_istirahat' => false, 'urutan' => 6],
            ['nama_jam' => 'Jam Keenam', 'waktu_mulai' => '10:20', 'waktu_selesai' => '10:50', 'is_istirahat' => false, 'urutan' => 7],
            ['nama_jam' => 'Jam Ketujuh', 'waktu_mulai' => '11:00', 'waktu_selesai' => '11:35', 'is_istirahat' => false, 'urutan' => 8],
            ['nama_jam' => 'Jam Kedelapan', 'waktu_mulai' => '11:35', 'waktu_selesai' => '12:10', 'is_istirahat' => false, 'urutan' => 9],
        ];

        $levels = Level::all();
        foreach ($slots as $slot) {
            $timeSlot = TimeSlot::updateOrCreate(
                ['nama_jam' => $slot['nama_jam']],
                $slot
            );

            // Filter Level: Kelas 1 & 2 maksimal sampai jam ke-6 (urutan 7)
            $targetLevelIds = collect($levels)->filter(function ($level) use ($slot) {
                if (in_array($level->nama_tingkatan, ['Kelas 1', 'Kelas 2'])) {
                    return $slot['urutan'] <= 7; // Jam 1-6 + Istirahat
                }

                return true; // Kelas lain sampai jam 8
            })->pluck('id');

            $timeSlot->levels()->sync($targetLevelIds);
        }
    }

    public function run(): void
    {
        $this->up();
    }
}
