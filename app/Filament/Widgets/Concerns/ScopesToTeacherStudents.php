<?php

namespace App\Filament\Widgets\Concerns;

use App\Models\StudyGroup;
use Illuminate\Support\Facades\Auth;

trait ScopesToTeacherStudents
{
    /**
     * Get the study groups where the authenticated user is the wali kelas.
     */
    protected function getTeacherStudyGroups()
    {
        $user = Auth::user();

        if (! $user || ! $user->teacher) {
            return collect();
        }

        return $user->teacher->studyGroups;
    }

    /**
     * Get all student IDs from the teacher's study groups.
     */
    protected function getTeacherStudentIds(): array
    {
        $studyGroups = $this->getTeacherStudyGroups();

        if ($studyGroups->isEmpty()) {
            return [];
        }

        return StudyGroup::whereIn('id', $studyGroups->pluck('id'))
            ->with('students')
            ->get()
            ->pluck('students')
            ->flatten()
            ->pluck('id')
            ->unique()
            ->toArray();
    }

    /**
     * Scope a query to only include the teacher's students.
     */
    protected function scopeTeacherStudents($query)
    {
        $studentIds = $this->getTeacherStudentIds();

        if (empty($studentIds)) {
            return $query->whereRaw('1 = 0'); // Return empty result
        }

        return $query->whereIn('student_id', $studentIds);
    }

    /**
     * Scope a query to only include attendance for the teacher's students.
     */
    protected function scopeTeacherAttendance($query)
    {
        $studentIds = $this->getTeacherStudentIds();

        if (empty($studentIds)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('student', function ($q) use ($studentIds) {
            $q->whereIn('id', $studentIds);
        });
    }

    /**
     * Scope a query to only include grades for the teacher's students.
     */
    protected function scopeTeacherGrades($query)
    {
        $studentIds = $this->getTeacherStudentIds();

        if (empty($studentIds)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('student_id', $studentIds);
    }

    /**
     * Scope a query to only include leave requests for the teacher's students.
     */
    protected function scopeTeacherLeaves($query)
    {
        $studentIds = $this->getTeacherStudentIds();

        if (empty($studentIds)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('student', function ($q) use ($studentIds) {
            $q->whereIn('id', $studentIds);
        });
    }
}
