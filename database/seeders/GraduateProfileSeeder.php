<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\GraduateProfile;
use Illuminate\Database\Seeder;

class GraduateProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        if (! $activeYear) {
            return;
        }

        $profilesData = [
            'Keimanan dan Ketakwaan terhadap Tuhan Yang Maha Esa' => [
                'Hubungan dengan Tuhan Yang Maha Esa',
                'Hubungan dengan sesama manusia',
                'Hubungan dengan Lingkungan Alam',
            ],
            'Kewargaan' => [
                'Kewargaan Nasional',
                'Kewargaan Global',
            ],
            'Penalaran Kritis' => [
                'Penyampaian Argumentasi',
                'Pengambilan Keputusan',
                'Penyelesaian Masalah',
            ],
            'Kreativitas' => [
                'Gagasan baru',
                'Fleksibilitas berpikir',
                'Karya',
            ],
            'Kemandirian' => [
                'Bertanggung Jawab',
                'Kepemimpinan',
                'Pengembangan Diri',
            ],
            'Kolaborasi' => [
                'Peduli',
                'Berbagi',
                'Kerja sama',
            ],
            'Komunikasi' => [
                'Berbicara',
                'Membaca',
                'Menulis',
            ],
            'Kesehatan' => [
                'Hidup bersih dan sehat',
                'Kebugaran, kesehatan fisik, dan kesehatan mental',
                'Kesehatan Lingkungan',
            ],
        ];

        foreach ($profilesData as $dimensi => $subdimensions) {
            $profile = GraduateProfile::updateOrCreate(
                [
                    'academic_year_id' => $activeYear->id,
                    'dimensi' => $dimensi,
                ],
                [
                    'academic_year_id' => $activeYear->id,
                    'dimensi' => $dimensi,
                ]
            );

            // Delete existing subdimensions
            $profile->subdimensions()->delete();

            // Create new subdimensions
            foreach ($subdimensions as $subdimensi) {
                $profile->subdimensions()->create([
                    'subdimensi' => $subdimensi,
                ]);
            }
        }
    }
}
