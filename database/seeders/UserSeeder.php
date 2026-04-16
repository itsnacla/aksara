<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\Staff;
use App\Models\Level;
use App\Models\Classroom;
use App\Models\AcademicYear;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Definisikan Role
        $roles = ['super_admin', 'staff', 'guru', 'siswa', 'wali'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // 2. Data Master Dasar
        $academicYear = AcademicYear::firstOrCreate(
            ['tahun_ajaran' => '2025/2026', 'semester' => 'ganjil'],
            ['is_active' => true]
        );

        $level10 = Level::firstOrCreate(['nama_tingkatan' => 'Kelas 10']);

        // 3. Buat Users & Profiles
        
        // --- SUPER ADMIN ---
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@aksara.com'],
            [
                'name' => 'Super Admin Aksara',
                'username' => 'admin',
                'password' => Hash::make('password'),
            ]
        );
        $adminUser->syncRoles(['super_admin']);

        // --- STAFF ---
        $staffUser = User::firstOrCreate(
            ['email' => 'staff@aksara.com'],
            [
                'name' => 'Staff Tata Usaha',
                'username' => 'staff',
                'password' => Hash::make('password'),
            ]
        );
        $staffUser->syncRoles(['staff']);
        Staff::firstOrCreate(
            ['user_id' => $staffUser->id],
            [
                'jabatan' => 'Administrasi Umum',
                'no_whatsapp' => '081234567890'
            ]
        );

        // --- GURU ---
        $guruUser = User::firstOrCreate(
            ['email' => 'guru@aksara.com'],
            [
                'name' => 'Guru Demo',
                'username' => 'guru',
                'password' => Hash::make('password'),
            ]
        );
        $guruUser->syncRoles(['guru']);
        $teacher = Teacher::firstOrCreate(
            ['user_id' => $guruUser->id],
            [
                'nip' => '19870101202001',
                'spesialisasi' => 'Matematika',
                'is_walikelas' => true,
                'no_whatsapp' => '082345678901'
            ]
        );

        // --- KELAS ---
        $classroom = Classroom::firstOrCreate(
            ['nama_kelas' => 'X-IPA-1', 'level_id' => $level10->id],
            ['walikelas_id' => $teacher->id]
        );

        // --- WALI (PARENT) ---
        $waliUser = User::firstOrCreate(
            ['email' => 'wali@aksara.com'],
            [
                'name' => 'Orang Tua Murid',
                'username' => 'wali',
                'password' => Hash::make('password'),
            ]
        );
        $waliUser->syncRoles(['wali']);
        $parent = StudentParent::firstOrCreate(
            ['user_id' => $waliUser->id],
            [
                'hubungan' => 'ayah',
                'no_whatsapp' => '083456789012'
            ]
        );

        // --- SISWA ---
        $siswaUser = User::firstOrCreate(
            ['email' => 'siswa@aksara.com'],
            [
                'name' => 'Siswa Percobaan',
                'username' => 'siswa',
                'password' => Hash::make('password'),
            ]
        );
        $siswaUser->syncRoles(['siswa']);
        Student::firstOrCreate(
            ['user_id' => $siswaUser->id],
            [
                'classroom_id' => $classroom->id,
                'parent_id' => $parent->id,
                'nisn' => '0012345678',
            ]
        );
    }
}