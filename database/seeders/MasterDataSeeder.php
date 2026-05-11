<?php
 
namespace Database\Seeders;
 
use App\Models\AcademicYear;
use App\Models\Level;
use App\Models\Subject;
use App\Models\Classroom;
use App\Models\TimeSlot;
use App\Models\DayConfig;
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
        $rooms = ['Ruang 1', 'Ruang 2A', 'Ruang 2B', 'Ruang 3', 'Ruang 4', 'Ruang 5', 'Ruang 6'];
        foreach ($rooms as $roomName) {
            Classroom::firstOrCreate(['nama_ruangan' => $roomName]);
        }
    }
 
    private function seedLevels(): array
    {
        $levels = [
            'Kelas 1' => 1, 'Kelas 2' => 2, 'Kelas 3' => 3,
            'Kelas 4' => 4, 'Kelas 5' => 5, 'Kelas 6' => 6
        ];
        $levelModels = [];
        foreach ($levels as $name => $sort) {
            $levelModels[$sort] = Level::firstOrCreate(['nama_tingkatan' => $name]);
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
 
}
