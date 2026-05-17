<?php

namespace Database\Seeders;

use App\Models\GraduateProfile;
use Illuminate\Database\Seeder;

class GraduateProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profiles = [
            [
                'dimensi' => 'keimanan dan ketakwaan terhadap Tuhan Yang Maha Esa',
                'subdimensi' => 'Hubungan dengan Tuhan Yang Maha Esa'
            ],
            [
                'dimensi' => 'keimanan dan ketakwaan terhadap Tuhan Yang Maha Esa',
                'subdimensi' => 'Hubungan dengan sesama manusia'
            ],
            [
                'dimensi' => 'keimanan dan ketakwaan terhadap Tuhan Yang Maha Esa',
                'subdimensi' => 'Hubungan dengan Lingkungan Alam'
            ],
            [
                'dimensi' => 'kewargaan',
                'subdimensi' => 'Kewargaan Nasional'
            ],
            [
                'dimensi' => 'kewargaan',
                'subdimensi' => 'Kewargaan Global'
            ],
            [
                'dimensi' => 'penalaran kritis',
                'subdimensi' => 'Penyampaian Argumentasi'
            ],
            [
                'dimensi' => 'penalaran kritis',
                'subdimensi' => 'Pengambilan Keputusan'
            ],
            [
                'dimensi' => 'penalaran kritis',
                'subdimensi' => 'Penyelesaian Masalah'
            ],
            [
                'dimensi' => 'kreativitas',
                'subdimensi' => 'Gagasan baru'
            ],
            [
                'dimensi' => 'kreativitas',
                'subdimensi' => 'Fleksibilitas berpikir'
            ],
            [
                'dimensi' => 'kreativitas',
                'subdimensi' => 'Karya'
            ],
            [
                'dimensi' => 'kemandirian',
                'subdimensi' => 'Bertanggung Jawab'
            ],
            [
                'dimensi' => 'kemandirian',
                'subdimensi' => 'Kepemimpinan'
            ],
            [
                'dimensi' => 'kemandirian',
                'subdimensi' => 'Pengembangan Diri'
            ],
            [
                'dimensi' => 'kolaborasi',
                'subdimensi' => 'Peduli'
            ],
            [
                'dimensi' => 'kolaborasi',
                'subdimensi' => 'Berbagi'
            ],
            [
                'dimensi' => 'kolaborasi',
                'subdimensi' => 'Kerja sama'
            ],
            [
                'dimensi' => 'komunikasi',
                'subdimensi' => 'Berbicara'
            ],
            [
                'dimensi' => 'komunikasi',
                'subdimensi' => 'Membaca'
            ],
            [
                'dimensi' => 'komunikasi',
                'subdimensi' => 'Menulis'
            ],
            [
                'dimensi' => 'kesehatan',
                'subdimensi' => 'Hidup bersih dan sehat'
            ],
            [
                'dimensi' => 'kesehatan',
                'subdimensi' => 'Kebugaran, kesehatan fisik, dan kesehatan mental'
            ],
            [
                'dimensi' => 'kesehatan',
                'subdimensi' => 'Kesehatan Lingkungan'
            ],
        ];

        foreach ($profiles as $profile) {
            GraduateProfile::updateOrCreate(
                [
                    'dimensi' => $profile['dimensi'],
                    'subdimensi' => $profile['subdimensi']
                ],
                $profile
            );
        }
    }
}
