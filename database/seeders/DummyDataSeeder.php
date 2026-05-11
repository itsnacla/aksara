<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Teacher;
use App\Models\StudyGroup;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\AcademicYear;
use App\Models\Level;
use App\Models\Staff;
use App\Models\Classroom;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    protected $academicYears;
    protected $teachers = [];

    public function run(): void
    {
        $this->academicYears = AcademicYear::where('tahun_ajaran', '2025/2026')->get();
        if ($this->academicYears->isEmpty()) return;

        $this->seedWaliKelas();
        $this->seedGuruMapel();
        $this->seedStaff();
        $this->seedRombelAndStudents();
    }

    protected function seedWaliKelas()
    {
        $data = [
            ['name' => 'Eni Nuryanti, S.Pd, Gr.', 'username' => 'eni', 'level' => 'Kelas 1', 'room' => 'Ruang 1'],
            ['name' => 'Rustiningsih, S.Pd', 'username' => 'rusti', 'level' => 'Kelas 2', 'room' => 'Ruang 2A', 'rombel_suffix' => 'A'],
            ['name' => 'Fertiko Yoga Lukmana S.Pd', 'username' => 'fertiko', 'level' => 'Kelas 2', 'room' => 'Ruang 2B', 'rombel_suffix' => 'B'],
            ['name' => 'Alex Nicho Bastyan, S.Pd', 'username' => 'alex', 'level' => 'Kelas 3', 'room' => 'Ruang 3'],
            ['name' => 'Drs. Imam Fahrudin', 'username' => 'imam', 'level' => 'Kelas 4', 'room' => 'Ruang 4'],
            ['name' => 'Yusril Lufi Habibi, S.Pd', 'username' => 'yusril', 'level' => 'Kelas 5', 'room' => 'Ruang 5'],
            ['name' => 'Farid Ruridra, S.Pd', 'username' => 'farid', 'level' => 'Kelas 6', 'room' => 'Ruang 6'],
        ];

        foreach ($data as $item) {
            $user = User::create([
                'name' => $item['name'],
                'username' => $item['username'],
                'email' => $item['username'] . '@aksara.com',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);
            $user->assignRole('guru');

            $this->teachers[$item['username']] = Teacher::create([
                'user_id' => $user->id,
                'nip' => (string)rand(1000000000, 9999999999),
                'is_walikelas' => true,
                'status' => 'aktif',
            ]);

            // Simpan metadata untuk pembuatan rombel nanti
            $this->teachers[$item['username']]->temp_level = $item['level'];
            $this->teachers[$item['username']]->temp_room = $item['room'];
            $this->teachers[$item['username']]->temp_suffix = $item['rombel_suffix'] ?? null;
        }
    }

    protected function seedGuruMapel()
    {
        $data = [
            ['name' => 'Beni Putra, S.Pd', 'username' => 'beni', 'subjects' => ['Pendidikan Jasmani, Olahraga dan Kesehatan']],
            ['name' => 'Angger Wigunaning Aji, S.Pd', 'username' => 'angger', 'subjects' => ['Bahasa Inggris', 'Bahasa Using', 'Bahasa Jawa']],
            ['name' => 'Moh. Itqonur Risal, S.Pd', 'username' => 'risal', 'subjects' => ['Pendidikan Agama']],
        ];

        foreach ($data as $item) {
            $user = User::create([
                'name' => $item['name'],
                'username' => $item['username'],
                'email' => $item['username'] . '@aksara.com',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);
            $user->assignRole('guru');

            $teacher = Teacher::create([
                'user_id' => $user->id,
                'nip' => (string)rand(1000000000, 9999999999),
                'is_walikelas' => false,
                'status' => 'aktif',
            ]);

            $subjectIds = Subject::whereIn('nama_mapel', $item['subjects'])->pluck('id');
            $teacher->subjects()->sync($subjectIds);
        }

        // CONTOH GURU NON-AKTIF (MUTASI)
        $userMutasi = User::create([
            'name' => 'Guru Mutasi, S.Pd', 'username' => 'mutasi', 'email' => 'mutasi@aksara.com',
            'password' => Hash::make('password'), 'is_active' => false,
        ]);
        $userMutasi->assignRole('guru');
        Teacher::create([
            'user_id' => $userMutasi->id, 'nip' => '1234567890', 'is_walikelas' => false, 'status' => 'mutasi'
        ]);
    }

    protected function seedStaff()
    {
        $data = [
            ['name' => 'Siti Sarah', 'username' => 'sarah', 'jabatan' => 'Bendahara'],
            ['name' => 'Bambang Irawan', 'username' => 'bambang', 'jabatan' => 'Administrasi Umum'],
        ];

        foreach ($data as $item) {
            $user = User::create([
                'name' => $item['name'],
                'username' => $item['username'],
                'email' => $item['username'] . '@aksara.com',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);
            $user->assignRole('staff');

            Staff::create([
                'user_id' => $user->id,
                'jabatan' => $item['jabatan'],
                'status' => 'aktif',
                'no_whatsapp' => '08' . rand(111111111, 999999999),
            ]);
        }
    }

    protected function seedRombelAndStudents()
    {
        foreach ($this->teachers as $teacher) {
            $level = Level::where('nama_tingkatan', $teacher->temp_level)->first();
            $room = Classroom::where('nama_ruangan', $teacher->temp_room)->first();
            
            $rombelIds = [];

            foreach ($this->academicYears as $ay) {
                $rombel = StudyGroup::updateOrCreate(
                    [
                        'level_id' => $level->id,
                        'classroom_id' => $room->id,
                        'academic_year_id' => $ay->id,
                    ],
                    [
                        'walikelas_id' => $teacher->id,
                        'nama_rombel' => "{$teacher->temp_level} - {$teacher->temp_room}",
                    ]
                );
                $rombelIds[] = $rombel->id;
            }

            // Masukkan siswa ke SEMUA rombel yang baru dibuat (Ganjil & Genap)
            for ($i = 1; $i <= 20; $i++) {
                $this->createStudent($rombelIds, $i, "{$teacher->temp_level} - {$teacher->temp_room}");
            }
        }

        // CONTOH SISWA LULUS
        for ($j = 1; $j <= 5; $j++) {
            $name = "Siswa Lulus No $j";
            $username = "lulus$j";

            // User Orang Tua
            $uWali = User::create([
                'name' => "Wali $name",
                'username' => "wali$username",
                'email' => "walilulus$j@aksara.com",
                'password' => Hash::make('password'),
                'is_active' => false,
            ]);
            $uWali->assignRole('wali');
            $parent = StudentParent::create(['user_id' => $uWali->id, 'hubungan' => 'ayah']);

            // User Siswa
            $u = User::create([
                'name' => $name,
                'username' => $username,
                'email' => "lulus$j@aksara.com",
                'password' => Hash::make('password'),
                'is_active' => false,
            ]);
            $u->assignRole('siswa');
            
            Student::create([
                'user_id' => $u->id, 
                'parent_id' => $parent->id,
                'nisn' => "999000$j", 
                'status' => 'lulus', 
                'gender' => 'L'
            ]);
        }
    }

    protected function createStudent($rombelIds, $index, $namaRombel)
    {
        $name = "Siswa " . $namaRombel . " No " . $index;
        $username = strtolower(str_replace([' ', '.'], '', $name));

        // Parent
        $parentUser = User::create([
            'name' => 'Wali ' . $name,
            'username' => 'wali' . $username,
            'email' => 'wali' . $username . '@aksara.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $parentUser->assignRole('wali');
        $parent = StudentParent::create(['user_id' => $parentUser->id, 'hubungan' => 'ayah']);

        // Student
        $studentUser = User::create([
            'name' => $name,
            'username' => $username,
            'email' => $username . '@aksara.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $studentUser->assignRole('siswa');

        $student = Student::create([
            'user_id' => $studentUser->id,
            'parent_id' => $parent->id,
            'status' => 'aktif',
            'nisn' => rand(1000000000, 9999999999),
            'gender' => ($index % 2 == 0) ? 'L' : 'P',
        ]);

        // Hubungkan ke semua Rombel tahun ini
        $student->studyGroups()->sync($rombelIds);
    }
}
