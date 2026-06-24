<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\SchoolSetting;
use App\Models\Teacher;
use App\Models\AcademicYear;
use App\Services\Academic\RaporService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrintController extends Controller
{
    /**
     * Print Pelengkap Rapor
     */
    public function printPelengkapRapor(Student $student): View
    {
        $school = SchoolSetting::current();
        $principal = Teacher::with('user')->where('is_kepalasekolah', true)->first();
        
        $student->load(['user', 'parent', 'studyGroups.level']);
        $rombel = $student->currentStudyGroup();

        return view('reports.pelengkap-rapor', compact('student', 'school', 'principal', 'rombel'));
    }

    /**
     * Print Rapor for Student
     */
    public function printRapor(Student $student, Request $request): View
    {
        $academicYearId = (int)($request->input('academic_year_id') ?: AcademicYear::where('is_active', true)->value('id'));
        if (!$academicYearId) {
            abort(400, 'Tahun ajaran aktif tidak ditemukan.');
        }

        // Security check for parents and students
        $user = auth()->user();
        if ($user && ($user->hasRole('siswa') || $user->hasRole('wali'))) {
            $isPublished = \App\Models\StudentRapor::where('student_id', $student->id)
                ->where('academic_year_id', $academicYearId)
                ->where('is_published', true)
                ->exists();
            if (!$isPublished) {
                abort(403, 'Rapor Anda belum dipublikasikan oleh Wali Kelas.');
            }
        }

        $raporService = new RaporService();
        $raporData = $raporService->getStudentRaporData($student, $academicYearId);

        return view('reports.rapor', $raporData);
    }

    /**
     * Print Rapor Bulk for Multiple Students
     */
    public function printRaporBulk(Request $request): View
    {
        $studentIds = explode(',', $request->input('student_ids'));
        $academicYearId = (int)($request->input('academic_year_id') ?: AcademicYear::where('is_active', true)->value('id'));
        if (!$academicYearId) {
            abort(400, 'Tahun ajaran aktif tidak ditemukan.');
        }

        $students = Student::whereIn('id', $studentIds)->get();
        $raporService = new RaporService();

        $studentsData = [];
        /** @var Student $student */
        foreach ($students as $student) {
            $studentsData[] = $raporService->getStudentRaporData($student, $academicYearId);
        }

        return view('reports.rapor', [
            'isBulk' => true,
            'reports' => $studentsData,
        ]);
    }

    /**
     * Print Pelengkap Rapor Bulk for Multiple Students
     */
    public function printPelengkapRaporBulk(Request $request): View
    {
        $studentIds = explode(',', $request->input('student_ids'));
        $school = SchoolSetting::current();
        $principal = Teacher::with('user')->where('is_kepalasekolah', true)->first();

        $students = Student::with(['user', 'parent', 'studyGroups.level'])
            ->whereIn('id', $studentIds)
            ->get();

        $reports = [];
        foreach ($students as $student) {
            $reports[] = [
                'student' => $student,
                'school' => $school,
                'principal' => $principal,
                'rombel' => $student->currentStudyGroup(),
            ];
        }

        return view('reports.pelengkap-rapor', [
            'isBulk' => true,
            'reports' => $reports,
        ]);
    }

    /**
     * Print Buku Induk for Level 1 / Kelas 1
     */
    public function printBukuInduk(Student $student): View
    {
        $school = SchoolSetting::current();
        $principal = Teacher::with('user')->where('is_kepalasekolah', true)->first();
        
        $student->load(['user', 'parent', 'studyGroups.level']);
        $rombel = $student->currentStudyGroup();

        $dataBuilder = new \App\Services\Academic\BukuIndukDataBuilder();
        $chunkedData = $dataBuilder->getBukuIndukData($student);

        return view('reports.buku-induk', [
            'records' => [
                [
                    'student' => $student,
                    'school' => $school,
                    'principal' => $principal,
                    'rombel' => $rombel,
                    'chunkedData' => $chunkedData,
                ]
            ]
        ]);
    }

    /**
     * Print Buku Induk Bulk for Multiple Students
     */
    public function printBukuIndukBulk(Request $request): View
    {
        $studentIds = explode(',', $request->input('student_ids'));
        $school = SchoolSetting::current();
        $principal = Teacher::with('user')->where('is_kepalasekolah', true)->first();

        $students = Student::with(['user', 'parent', 'studyGroups.level'])
            ->whereIn('id', $studentIds)
            ->get();

        $dataBuilder = new \App\Services\Academic\BukuIndukDataBuilder();
        $reports = [];
        foreach ($students as $student) {
            $reports[] = [
                'student' => $student,
                'school' => $school,
                'principal' => $principal,
                'rombel' => $student->currentStudyGroup(),
                'chunkedData' => $dataBuilder->getBukuIndukData($student),
            ];
        }

        return view('reports.buku-induk', [
            'isBulk' => true,
            'records' => $reports, // The scaffold uses $records
        ]);
    }
}
