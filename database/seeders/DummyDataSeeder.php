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
        // Seed the random number generators to make Faker and all random functions 100% deterministic!
        srand(42);
        mt_srand(42);

        $this->academicYears = AcademicYear::where('tahun_ajaran', '2025/2026')->get();
        if ($this->academicYears->isEmpty()) return;

        $this->seedWaliKelas();
        $this->seedGuruMapel();
        $this->seedStaff();
        $this->seedRombelAndStudents();
        $this->seedAttendance();
    }

    protected function seedWaliKelas()
    {
        $data = [
            ['gd' => '', 'name' => 'Eni Nuryanti', 'gb' => 'S.Pd, Gr.', 'username' => 'eni', 'level' => 'Kelas 1', 'room' => 'A'],
            ['gd' => '', 'name' => 'Rustiningsih', 'gb' => 'S.Pd', 'username' => 'rusti', 'level' => 'Kelas 2', 'room' => 'A'],
            ['gd' => '', 'name' => 'Fertiko Yoga Lukmana', 'gb' => 'S.Pd', 'username' => 'fertiko', 'level' => 'Kelas 2', 'room' => 'B'],
            ['gd' => '', 'name' => 'Alex Nicho Bastyan', 'gb' => 'S.Pd', 'username' => 'alex', 'level' => 'Kelas 3', 'room' => 'A'],
            ['gd' => 'Drs.', 'name' => 'Imam Fahrudin', 'gb' => '', 'username' => 'imam', 'level' => 'Kelas 4', 'room' => 'A'],
            ['gd' => '', 'name' => 'Yusril Lufi Habibi', 'gb' => 'S.Pd', 'username' => 'yusril', 'level' => 'Kelas 5', 'room' => 'A'],
            ['gd' => '', 'name' => 'Farid Ruridra', 'gb' => 'S.Pd', 'username' => 'farid', 'level' => 'Kelas 6', 'room' => 'A'],
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
                'gelar_depan' => $item['gd'],
                'gelar_belakang' => $item['gb'],
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
            ['gd' => '', 'name' => 'Beni Putra', 'gb' => 'S.Pd', 'username' => 'beni', 'subjects' => ['Pendidikan Jasmani, Olahraga dan Kesehatan']],
            ['gd' => '', 'name' => 'Angger Wigunaning Aji', 'gb' => 'S.Pd', 'username' => 'angger', 'subjects' => ['Bahasa Inggris', 'Bahasa Using', 'Bahasa Jawa']],
            ['gd' => '', 'name' => 'Moh. Itqonur Risal', 'gb' => 'S.Pd', 'username' => 'risal', 'subjects' => ['Pendidikan Agama']],
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
                'gelar_depan' => $item['gd'],
                'gelar_belakang' => $item['gb'],
                'nip' => (string)rand(1000000000, 9999999999),
                'is_walikelas' => false,
                'status' => 'aktif',
            ]);

            $subjectIds = Subject::whereIn('nama_mapel', $item['subjects'])->pluck('id');
            $teacher->subjects()->sync($subjectIds);
        }

        // CONTOH GURU NON-AKTIF (MUTASI)
        $userMutasi = User::create([
            'name' => 'Guru Mutasi', 'username' => 'mutasi', 'email' => 'mutasi@aksara.com',
            'password' => Hash::make('password'), 'is_active' => false,
        ]);
        $userMutasi->assignRole('guru');
        Teacher::create([
            'user_id' => $userMutasi->id, 
            'gelar_depan' => '', 
            'gelar_belakang' => 'S.Pd', 
            'nip' => '1234567890', 
            'is_walikelas' => false, 
            'status' => 'mutasi'
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
            $parent = StudentParent::create([
                'user_id' => $uWali->id, 
                'hubungan' => 'ayah',
                'father_name' => "Ayah $name",
                'mother_name' => "Ibu $name",
                'address' => 'Jl. Pendidikan No. ' . $j,
                'province' => 'JAWA TIMUR',
                'city' => 'KABUPATEN BANYUWANGI',
                'district' => 'PESANGGARAN',
                'village' => 'PESANGGARAN',
            ]);

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
        $firstNames = ['Ahmad', 'Budi', 'Cici', 'Dedi', 'Eka', 'Fani', 'Gani', 'Hani', 'Indra', 'Jaka', 'Kiki', 'Lulu', 'Maman', 'Nina', 'Oki', 'Putri', 'Rian', 'Siti', 'Tono', 'Umar'];
        $lastNames = ['Saputra', 'Wijaya', 'Lestari', 'Hidayat', 'Kusuma', 'Santoso', 'Pratiwi', 'Fauzi', 'Ramadhan', 'Sari'];
        $religions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha'];
        $places = ['Banyuwangi', 'Jakarta', 'Surabaya', 'Malang', 'Bandung', 'Yogyakarta'];
        
        $name = $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
        $uniqueId = substr(md5($name . '_' . $namaRombel . '_' . $index), 0, 6);
        $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name)) . '_' . $uniqueId . '_' . $index;

        // Parent Data
        $fatherName = "Bapak " . $firstNames[array_rand($firstNames)] . " " . $lastNames[array_rand($lastNames)];
        $motherName = "Ibu " . $firstNames[array_rand($firstNames)] . " " . $lastNames[array_rand($lastNames)];

        // Parent Account
        $parentUser = User::create([
            'name' => $fatherName,
            'username' => 'wali_' . $username,
            'email' => 'wali_' . $username . '@aksara.samastanuswantara.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $parentUser->assignRole('wali');

        $occupations = ['PNS', 'Karyawan Swasta', 'Wiraswasta', 'Petani', 'Buruh', 'Guru', 'Pedagang'];

        $parent = StudentParent::create([
            'user_id' => $parentUser->id, 
            'hubungan' => 'ayah',
            'father_name' => $fatherName,
            'father_occupation' => $occupations[array_rand($occupations)],
            'mother_name' => $motherName,
            'mother_occupation' => $occupations[array_rand($occupations)],
            'no_whatsapp' => '08' . rand(100000000, 999999999),
            'address' => 'Jl. Mawar No. ' . rand(1, 100),
            'province' => 'JAWA TIMUR',
            'city' => 'KABUPATEN BANYUWANGI',
            'district' => 'PESANGGARAN',
            'village' => 'PESANGGARAN',
            // Guardian data (dummy for consistency)
            'guardian_name' => ($index % 5 == 0) ? 'Wali ' . $name : null,
            'guardian_occupation' => ($index % 5 == 0) ? $occupations[array_rand($occupations)] : null,
            'guardian_address' => ($index % 5 == 0) ? 'Jl. Wali No. ' . rand(1, 10) : null,
        ]);

        // Student Account
        $studentUser = User::create([
            'name' => $name,
            'username' => $username,
            'email' => $username . '@aksara.samastanuswantara.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $studentUser->assignRole('siswa');

        $student = Student::create([
            'user_id' => $studentUser->id,
            'parent_id' => $parent->id,
            'status' => 'aktif',
            'nisn' => '00' . rand(10000000, 99999999),
            'nis' => rand(1000, 9999),
            'gender' => ($index % 2 == 0) ? 'L' : 'P',
            'pob' => $places[array_rand($places)],
            'dob' => now()->subYears(rand(7, 12))->subDays(rand(1, 365)),
            'religion' => $religions[array_rand($religions)],
            'phone' => '08' . rand(100000000, 999999999),
            'lives_with_parent' => true, // Defaulting to true for demo
            'address' => null, // Inherited from parent
            'previous_school' => 'TK Dharma Wanita ' . rand(1, 5),
        ]);

        // Connect to Rombels
        $student->studyGroups()->sync($rombelIds);
    }



    protected function seedAttendance(): void
    {
        $studyGroups = StudyGroup::with('students')->get();
        $startDate = now()->subDays(30);
        $endDate = now();

        $attendances = [];

        for ($date = clone $startDate; $date->lte($endDate); $date->addDay()) {
            if ($date->isWeekend()) {
                continue;
            }

            foreach ($studyGroups as $sg) {
                foreach ($sg->students as $student) {
                    $rand = rand(1, 100);
                    if ($rand <= 95) {
                        $status = 'hadir';
                    } elseif ($rand <= 98) {
                        $status = 'sakit';
                    } elseif ($rand == 99) {
                        $status = 'izin';
                    } else {
                        $status = 'alpha';
                    }

                    $attendances[] = [
                        'student_id' => $student->id,
                        'study_group_id' => $sg->id,
                        'status' => $status,
                        'tanggal' => $date->format('Y-m-d'),
                        'check_in' => $status === 'hadir' ? '07:00:00' : null,
                        'check_out' => $status === 'hadir' ? '13:00:00' : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        foreach (array_chunk($attendances, 500) as $chunk) {
            \App\Models\Attendance::insert($chunk);
        }
    }
}
