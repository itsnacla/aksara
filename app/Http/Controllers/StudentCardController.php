<?php

namespace App\Http\Controllers;

use App\Models\SchoolSetting;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentCardController extends Controller
{
    public function print(Student $student)
    {
        $user = auth()->user();
        if (! $user) {
            abort(403, 'Akses ditolak.');
        }

        // Allow Admin, Staff, and Guru
        if (! $user->hasAnyRole(['super_admin', 'staff', 'guru'])) {
            // If Student, must be their own card
            if ($user->hasRole('siswa') && $student->user_id !== $user->id) {
                abort(403, 'Akses ditolak. Anda hanya diperbolehkan mencetak kartu Anda sendiri.');
            }
            // If Parent, must be their child's card
            if ($user->hasRole('orang_tua')) {
                $childIds = $user->parent?->students()->pluck('id')->toArray() ?? [];
                if (! in_array($student->id, $childIds)) {
                    abort(403, 'Akses ditolak. Anda hanya diperbolehkan mencetak kartu untuk anak Anda sendiri.');
                }
            }
        }

        $school = SchoolSetting::current();

        return view('portal.students.print-card', [
            'students' => collect([$student]),
            'school' => $school,
        ]);
    }

    public function bulkPrint(Request $request)
    {
        $user = auth()->user();
        if (! $user || ! $user->hasAnyRole(['super_admin', 'staff', 'guru'])) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mencetak kartu secara massal.');
        }

        $ids = explode(',', $request->ids);
        $students = Student::whereIn('id', $ids)->with('user')->get();
        $school = SchoolSetting::current();

        return view('portal.students.print-card', [
            'students' => $students,
            'school' => $school,
        ]);
    }

    public function printByStudyGroup($studyGroupId)
    {
        $user = auth()->user();
        if (! $user || ! $user->hasAnyRole(['super_admin', 'staff', 'guru'])) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mencetak kartu secara massal.');
        }

        $students = Student::whereHas('studyGroups', fn ($q) => $q->where('study_groups.id', $studyGroupId))
            ->with('user')
            ->get();
        $school = SchoolSetting::current();

        return view('portal.students.print-card', [
            'students' => $students,
            'school' => $school,
        ]);
    }

    public function allPrint(Request $request)
    {
        $user = auth()->user();
        if (! $user || ! $user->hasAnyRole(['super_admin', 'staff', 'guru'])) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mencetak kartu secara massal.');
        }

        $query = Student::query()->with('user');

        if ($request->has('academic_year_id')) {
            $query->whereHas('studyGroups', fn ($q) => $q->where('academic_year_id', $request->academic_year_id));
        }

        $students = $query->get();
        $school = SchoolSetting::current();

        return view('portal.students.print-card', [
            'students' => $students,
            'school' => $school,
        ]);
    }
}
