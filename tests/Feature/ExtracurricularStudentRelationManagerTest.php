<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\StudyGroup;
use App\Models\Extracurricular;
use App\Filament\Resources\Extracurriculars\RelationManagers\ExtracurricularStudentRelationManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Filament\Tables\Actions\AttachAction;

class ExtracurricularStudentRelationManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_search_and_attach_student_without_query_exception()
    {
        // 1. Create active academic year
        $academicYear = AcademicYear::create([
            'tahun_ajaran' => '2025/2026',
            'semester' => 'ganjil',
            'is_active' => true,
        ]);

        // 2. Create extracurricular (pilihan)
        $extracurricular = Extracurricular::create([
            'nama_ekskul' => 'Pramuka',
            'kategori' => 'pilihan',
            'pembina' => 'Budi',
            'deskripsi' => 'Deskripsi Ekskul',
        ]);

        // 3. Create student user, parent user, parent record, and student record
        $parentUser = User::create([
            'name' => 'Wali Jaka',
            'username' => 'wali_jaka',
            'email' => 'wali_jaka@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $parent = \App\Models\StudentParent::create([
            'user_id' => $parentUser->id,
            'hubungan' => 'ayah',
            'father_name' => 'Wali Jaka',
            'mother_name' => 'Ibu Jaka',
            'address' => 'Jl. Mawar',
            'province' => 'JAWA TIMUR',
            'city' => 'KABUPATEN BANYUWANGI',
            'district' => 'PESANGGARAN',
            'village' => 'PESANGGARAN',
        ]);

        $user = User::create([
            'name' => 'Jaka Saputra',
            'username' => 'jaka',
            'email' => 'jaka@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'parent_id' => $parent->id,
            'status' => 'aktif',
            'nisn' => '00123456',
            'nis' => '1234',
            'gender' => 'L',
        ]);

        // 4. Create Teacher, Level, Classroom, StudyGroup and connect student to it
        $teacherUser = User::create([
            'name' => 'Guru Wali',
            'username' => 'guru_wali',
            'email' => 'guru@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $teacher = \App\Models\Teacher::create([
            'user_id' => $teacherUser->id,
            'kode_guru' => 'T01',
            'status' => 'aktif',
            'is_walikelas' => true,
        ]);

        $level = \App\Models\Level::create([
            'nama_tingkatan' => 'Kelas 1',
        ]);

        $classroom = \App\Models\Classroom::create([
            'nama_ruangan' => 'Ruang 1',
        ]);

        $studyGroup = StudyGroup::create([
            'academic_year_id' => $academicYear->id,
            'level_id' => $level->id,
            'classroom_id' => $classroom->id,
            'walikelas_id' => $teacher->id,
            'nama_rombel' => 'Kelas 1 - A',
        ]);

        // 5. Bypass gates and login a dummy user
        \Illuminate\Support\Facades\Gate::before(fn() => true);

        $admin = User::create([
            'name' => 'Admin User',
            'username' => 'admin_test',
            'email' => 'admin_test@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $this->actingAs($admin);

        // 6. Set current panel and test Livewire component of ExtracurricularStudentRelationManager
        \Filament\Facades\Filament::setCurrentPanel(
            \Filament\Facades\Filament::getPanel('admin')
        );

        Livewire::test(ExtracurricularStudentRelationManager::class, [
            'ownerRecord' => $extracurricular,
            'pageClass' => 'App\Filament\Resources\Extracurriculars\Pages\EditExtracurricular',
        ])
            ->assertActionExists('attach')
            // Now, we retrieve the options using the options query or searching to ensure no query exception is thrown.
            ->callAction('attach', data: [
                'recordId' => $student->id,
            ]);

        // Verify the student is attached successfully
        $this->assertTrue($extracurricular->students()->where('students.id', $student->id)->exists());
    }
}
