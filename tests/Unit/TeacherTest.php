<?php

namespace Tests\Unit;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_it_formats_full_name_correctly_with_front_and_back_titles()
    {
        $user = User::create([
            'name' => 'Budi Santoso',
            'username' => 'budisantoso1',
            'email' => 'budi1@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'kode_guru' => 'T01',
            'status' => 'aktif',
            'gelar_depan' => 'Drs.',
            'gelar_belakang' => 'M.Pd.',
        ]);

        $this->assertEquals('Drs. Budi Santoso, M.Pd.', $teacher->nama_lengkap);
    }

    /** @test */
    public function test_it_formats_full_name_with_only_front_title()
    {
        $user = User::create([
            'name' => 'Budi Santoso',
            'username' => 'budisantoso2',
            'email' => 'budi2@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'kode_guru' => 'T02',
            'status' => 'aktif',
            'gelar_depan' => 'Drs.',
            'gelar_belakang' => null,
        ]);

        $this->assertEquals('Drs. Budi Santoso', $teacher->nama_lengkap);
    }

    /** @test */
    public function test_it_formats_full_name_with_only_back_title()
    {
        $user = User::create([
            'name' => 'Budi Santoso',
            'username' => 'budisantoso3',
            'email' => 'budi3@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'kode_guru' => 'T03',
            'status' => 'aktif',
            'gelar_depan' => null,
            'gelar_belakang' => 'M.Pd.',
        ]);

        $this->assertEquals('Budi Santoso, M.Pd.', $teacher->nama_lengkap);
    }

    /** @test */
    public function test_it_formats_full_name_with_no_titles()
    {
        $user = User::create([
            'name' => 'Budi Santoso',
            'username' => 'budisantoso4',
            'email' => 'budi4@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'kode_guru' => 'T04',
            'status' => 'aktif',
            'gelar_depan' => null,
            'gelar_belakang' => null,
        ]);

        $this->assertEquals('Budi Santoso', $teacher->nama_lengkap);
    }
}
