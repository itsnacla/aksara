<?php

namespace Database\Seeders;

use App\Models\StudyGroup;
use App\Services\Academic\ScheduleGeneratorService;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $studyGroupIds = StudyGroup::whereHas('academicYear', fn ($q) => $q->where('is_active', true))
            ->pluck('id')
            ->toArray();

        if (empty($studyGroupIds)) {
            $this->command->warn('Tidak ada study group aktif. Pastikan academic year aktif sudah ada.');

            return;
        }

        $this->command->info('Generating jadwal untuk '.count($studyGroupIds).' rombel...');

        app(ScheduleGeneratorService::class)->generate(
            studyGroupIds: $studyGroupIds,
            overwrite: true,
        );

        $this->command->info('Jadwal berhasil di-generate!');
    }
}
