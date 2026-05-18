<?php

namespace App\Services\Academic;

use App\Models\Student;

class BukuIndukService
{
    /**
     * Generate / Synchronize Buku Induk data for a student.
     *
     * @param Student $student
     * @param int $academicYearId
     * @return Student
     */
    public function generateStudentBukuInduk(Student $student, int $academicYearId): Student
    {
        // Pastikan relasi parent di-load untuk sinkronisasi
        if (!$student->relationLoaded('parent')) {
            $student->load('parent');
        }

        $parent = $student->parent;

        // Sinkronisasi data Ayah jika kosong di model Student
        if (empty($student->ayah_nama) && $parent) {
            $student->ayah_nama = $parent->father_name;
            $student->ayah_pekerjaan = $parent->father_occupation;
        }

        // Sinkronisasi data Ibu jika kosong di model Student
        if (empty($student->ibu_nama) && $parent) {
            $student->ibu_nama = $parent->mother_name;
            $student->ibu_pekerjaan = $parent->mother_occupation;
        }

        // Sinkronisasi data Wali jika kosong di model Student
        if (empty($student->wali_nama) && $parent) {
            $student->wali_nama = $parent->guardian_name;
            $student->wali_pekerjaan = $parent->guardian_occupation;
        }

        // Pastikan status siswa tersetting
        if (empty($student->status)) {
            $student->status = 'aktif';
        }

        $student->is_buku_induk_generated = true;
        $student->save();

        return $student;
    }
}
