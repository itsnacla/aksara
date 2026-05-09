<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\Student;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Kelas
        $kelas = [
            ['nama_kelas' => 'X IPA 1', 'tingkat' => 'X', 'jurusan' => 'IPA'],
            ['nama_kelas' => 'X IPS 1', 'tingkat' => 'X', 'jurusan' => 'IPS'],
            ['nama_kelas' => 'XI IPA 1', 'tingkat' => 'XI', 'jurusan' => 'IPA'],
            ['nama_kelas' => 'XII IPA 1', 'tingkat' => 'XII', 'jurusan' => 'IPA'],
        ];
        foreach ($kelas as $k) {
            SchoolClass::create($k);
        }

        // Mata Pelajaran
        $mapel = [
            ['nama_mapel' => 'Matematika', 'kode_mapel' => 'MTK'],
            ['nama_mapel' => 'Bahasa Indonesia', 'kode_mapel' => 'BIN'],
            ['nama_mapel' => 'Bahasa Inggris', 'kode_mapel' => 'BIG'],
            ['nama_mapel' => 'Fisika', 'kode_mapel' => 'FIS'],
            ['nama_mapel' => 'Kimia', 'kode_mapel' => 'KIM'],
        ];
        foreach ($mapel as $m) {
            Subject::create($m);
        }

        // Jadwal
        Schedule::create(['school_class_id' => 1, 'subject_id' => 1, 'hari' => 'Senin', 'jam_mulai' => '07:00', 'jam_selesai' => '08:30', 'guru' => 'Bu Rina']);
        Schedule::create(['school_class_id' => 1, 'subject_id' => 2, 'hari' => 'Senin', 'jam_mulai' => '08:30', 'jam_selesai' => '10:00', 'guru' => 'Pak Budi']);
        Schedule::create(['school_class_id' => 1, 'subject_id' => 3, 'hari' => 'Selasa', 'jam_mulai' => '07:00', 'jam_selesai' => '08:30', 'guru' => 'Bu Sari']);
        Schedule::create(['school_class_id' => 2, 'subject_id' => 1, 'hari' => 'Rabu', 'jam_mulai' => '07:00', 'jam_selesai' => '08:30', 'guru' => 'Pak Joko']);

        // Siswa
        Student::create(['nama_siswa' => 'Ahmad Rizki', 'nisn' => '1234567890', 'jenis_kelamin' => 'Laki-laki', 'school_class_id' => 1]);
        Student::create(['nama_siswa' => 'Siti Aminah', 'nisn' => '1234567891', 'jenis_kelamin' => 'Perempuan', 'school_class_id' => 1]);
        Student::create(['nama_siswa' => 'Budi Santoso', 'nisn' => '1234567892', 'jenis_kelamin' => 'Laki-laki', 'school_class_id' => 2]);
    }
}