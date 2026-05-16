<?php
 
namespace Database\Seeders;
 
use App\Models\AcademicYear;
use App\Models\Level;
use App\Models\Subject;
use App\Models\Classroom;
use App\Models\Extracurricular;
use Illuminate\Database\Seeder;
 
class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedAcademicYears();
        $this->seedRooms();
        $levelModels = $this->seedLevels();
        $this->seedSubjects($levelModels);
        $this->seedExtracurriculars();
        $this->seedChatbotSettings();
    }

    private function seedChatbotSettings(): void
    {
        \App\Models\ChatbotSetting::updateOrCreate(
            ['id' => 1],
            [
                'is_active' => true,
                'primary_provider' => 'google',
                'fallback_providers' => 'groq,openai',
                'settings' => [
                    'google' => [
                        'key' => env('GOOGLE_AI_API_KEY', ''),
                        'model' => 'gemini-2.0-flash',
                    ],
                    'openai' => [
                        'key' => env('OPENAI_API_KEY', ''),
                        'model' => 'gpt-4o-mini',
                        'url' => 'https://api.openai.com/v1',
                    ],
                    'groq' => [
                        'key' => env('GROQ_API_KEY', ''),
                        'model' => 'llama-3.3-70b-versatile',
                    ]
                ]
            ]
        );
    }
 
    private function seedAcademicYears(): void
    {
        $startYear = 2025;
        for ($i = 0; $i < 5; $i++) {
            $yearStr = ($startYear + $i) . '/' . ($startYear + $i + 1);
            
            AcademicYear::firstOrCreate(
                ['tahun_ajaran' => $yearStr],
                [
                    'semester' => 'ganjil',
                    'is_active' => ($i === 0)
                ]
            );
        }
    }
 
    private function seedRooms(): void
    {
        $rooms = ['A', 'B'];
        foreach ($rooms as $roomName) {
            Classroom::firstOrCreate(['nama_ruangan' => $roomName]);
        }
    }
 
    private function seedLevels(): array
    {
        $levels = [
            'Kelas 1' => ['sort' => 1, 'fase' => 'A'],
            'Kelas 2' => ['sort' => 2, 'fase' => 'A'],
            'Kelas 3' => ['sort' => 3, 'fase' => 'B'],
            'Kelas 4' => ['sort' => 4, 'fase' => 'B'],
            'Kelas 5' => ['sort' => 5, 'fase' => 'C'],
            'Kelas 6' => ['sort' => 6, 'fase' => 'C'],
        ];

        $levelModels = [];
        foreach ($levels as $name => $data) {
            $levelModels[$data['sort']] = Level::updateOrCreate(
                ['nama_tingkatan' => $name],
                [
                    'fase' => $data['fase'],
                    'is_last_level' => ($data['sort'] === 6)
                ]
            );
        }
        return $levelModels;
    }
 
    private function seedSubjects(array $levelModels): void
    {
        $subjects = [
            ['nama_mapel' => 'Bahasa Indonesia', 'is_umum' => true, 'total_jp' => 6],
            ['nama_mapel' => 'Bahasa Jawa', 'is_umum' => false, 'total_jp' => 2, 'scheduling_priority' => 2],
            ['nama_mapel' => 'Bahasa Using', 'is_umum' => false, 'total_jp' => 2, 'scheduling_priority' => 2],
            ['nama_mapel' => 'Bahasa Inggris', 'is_umum' => false, 'total_jp' => 2, 'scheduling_priority' => 2],
            ['nama_mapel' => 'Pendidikan Pancasila', 'is_umum' => true, 'total_jp' => 6],
            ['nama_mapel' => 'Pendidikan Agama', 'is_umum' => false, 'total_jp' => 4, 'scheduling_priority' => 3],
            ['nama_mapel' => 'Pendidikan Jasmani, Olahraga dan Kesehatan', 'is_umum' => false, 'total_jp' => 4, 'is_one_day_finish' => true, 'scheduling_priority' => 3],
            ['nama_mapel' => 'Ilmu Pengetahuan Alam dan Sosial', 'is_umum' => true, 'total_jp' => 6],
            ['nama_mapel' => 'Matematika', 'is_umum' => true, 'total_jp' => 6],
            ['nama_mapel' => 'Seni Budaya dan Prakarya', 'is_umum' => true, 'total_jp' => 4],
            ['nama_mapel' => 'Upacara', 'is_umum' => true, 'total_jp' => 1, 'scheduling_priority' => 3],
        ];
 
        foreach ($subjects as $s) {
            $subject = Subject::firstOrCreate(['nama_mapel' => $s['nama_mapel']], $s);
            
            $ids = collect($levelModels)
                ->filter(function($model, $sort) use ($s) {
                    if ($s['nama_mapel'] === 'Ilmu Pengetahuan Alam dan Sosial' && in_array($sort, [1, 2])) return false;
                    if ($s['nama_mapel'] === 'Bahasa Using' && in_array($sort, [1, 2, 3])) return false;
                    return true;
                })
                ->pluck('id')->toArray();
 
            $subject->levels()->sync($ids);
        }
    }

    private function seedExtracurriculars(): void
    {
        $ekskuls = [
            ['nama_ekskul' => 'Pramuka', 'kategori' => 'wajib', 'pembina' => 'Eni Nuryanti, S.Pd', 'nilai_minimum' => 'B', 'deskripsi' => 'Kegiatan kepanduan wajib untuk melatih kedisiplinan dan kemandirian.'],
            ['nama_ekskul' => 'Futsal', 'kategori' => 'pilihan', 'pembina' => 'Beni Putra, S.Pd', 'nilai_minimum' => 'C', 'deskripsi' => 'Olahraga minat bakat sepak bola dalam ruangan.'],
            ['nama_ekskul' => 'Tari Tradisional', 'kategori' => 'pilihan', 'pembina' => 'Siti Sarah', 'nilai_minimum' => 'B', 'deskripsi' => 'Melestarikan seni budaya melalui tarian daerah.'],
            ['nama_ekskul' => 'PMR', 'kategori' => 'pilihan', 'pembina' => 'Bambang Irawan', 'nilai_minimum' => 'B', 'deskripsi' => 'Pelatihan pertolongan pertama dan kesehatan sekolah.'],
        ];

        foreach ($ekskuls as $e) {
            Extracurricular::updateOrCreate(['nama_ekskul' => $e['nama_ekskul']], $e);
        }
    }
}
