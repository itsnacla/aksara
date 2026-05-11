<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\SchoolSetting;
use Illuminate\Http\Request;

class StudentCardController extends Controller
{
    public function print(Student $student)
    {
        $school = SchoolSetting::current();
        return view('students.print-card', [
            'students' => collect([$student]),
            'school' => $school
        ]);
    }

    public function bulkPrint(Request $request)
    {
        $ids = explode(',', $request->ids);
        $students = Student::whereIn('id', $ids)->with('user')->get();
        $school = SchoolSetting::current();

        return view('students.print-card', [
            'students' => $students,
            'school' => $school
        ]);
    }

    public function printByStudyGroup($studyGroupId)
    {
        $students = Student::whereHas('studyGroups', fn ($q) => $q->where('study_groups.id', $studyGroupId))
            ->with('user')
            ->get();
        $school = SchoolSetting::current();

        return view('students.print-card', [
            'students' => $students,
            'school' => $school
        ]);
    }
}
