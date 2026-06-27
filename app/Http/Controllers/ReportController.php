<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\SchoolSetting;
use App\Models\StudyGroup;
use App\Models\Teacher;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function attendance(Request $request)
    {
        $user = auth()->user();
        if (! $user || ! $user->hasAnyRole(['super_admin', 'staff', 'guru'])) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk melihat laporan ini.');
        }

        $studyGroupId = $request->input('study_group_id');
        $from = $request->input('from');
        $until = $request->input('until');

        $query = Attendance::with(['student.user']);

        // Check user role
        if ($user->hasRole('guru') && $user->teacher) {
            $teacherId = $user->teacher->id;
            $query->where(function ($q) use ($teacherId) {
                $q->whereHas('studyGroup', fn ($sq) => $sq->where('walikelas_id', $teacherId))
                    ->orWhereHas('schedule', fn ($sq) => $sq->where('teacher_id', $teacherId));
            });
        }

        if ($studyGroupId) {
            $query->where('study_group_id', $studyGroupId);
        }

        if ($from) {
            $query->whereDate('tanggal', '>=', $from);
        }

        if ($until) {
            $query->whereDate('tanggal', '<=', $until);
        }

        // Get the attendances
        $attendances = $query->orderBy('tanggal', 'asc')->get();

        // Calculate summary per student
        $summary = $this->calculateAttendanceSummary($attendances);

        // Get additional info
        $rombel = $studyGroupId ? StudyGroup::with(['level', 'classroom', 'academicYear', 'waliKelas.user'])->find($studyGroupId) : null;
        $school = SchoolSetting::first();
        $principal = Teacher::with('user')->where('is_kepalasekolah', true)->first();

        return view('reports.attendance', compact('attendances', 'summary', 'rombel', 'school', 'principal', 'from', 'until'));
    }

    private function calculateAttendanceSummary($attendances): array
    {
        $summary = [];

        foreach ($attendances as $attendance) {
            $studentId = $attendance->student_id;

            if (! isset($summary[$studentId])) {
                $summary[$studentId] = [
                    'name' => $attendance->student->user->name ?? 'Unknown',
                    'nisn' => $attendance->student->nisn ?? '-',
                    'hadir' => 0,
                    'sakit' => 0,
                    'izin' => 0,
                    'alpha' => 0,
                ];
            }

            $status = strtolower($attendance->status);
            if (isset($summary[$studentId][$status])) {
                $summary[$studentId][$status]++;
            }
        }

        // Sort summary alphabetically by student name
        usort($summary, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $summary;
    }

    public function schedule(Request $request)
    {
        $user = auth()->user();
        if (! $user || ! $user->hasAnyRole(['super_admin', 'staff', 'guru'])) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk melihat laporan ini.');
        }

        $studyGroupId = $request->input('study_group_id');
        $showSubjectCode = $request->input('show_subject_code', 1);
        $showTeacherCode = $request->input('show_teacher_code', 0);
        $academicYearId = AcademicYear::where('is_active', true)->first()?->id;

        $query = Schedule::with(['subject', 'teacher.user', 'studyGroup.level', 'startTimeSlot', 'endTimeSlot']);

        if ($studyGroupId && $studyGroupId !== 'all') {
            $query->where('study_group_id', $studyGroupId);
        } elseif ($academicYearId) {
            $query->whereHas('studyGroup', fn ($q) => $q->where('academic_year_id', $academicYearId));
        }

        $schedules = $query->orderBy('hari')->get();

        // Group by Rombel
        $groupedSchedules = $schedules->groupBy('study_group_id');

        // If 'all', we might want to include Rombols that have NO schedule yet (optional)
        if ($studyGroupId === 'all' || ! $studyGroupId) {
            $rombels = StudyGroup::with(['level', 'academicYear', 'waliKelas.user'])
                ->where('academic_year_id', $academicYearId)
                ->get();
        } else {
            $rombels = StudyGroup::with(['level', 'academicYear', 'waliKelas.user'])
                ->where('id', $studyGroupId)
                ->get();
        }

        $school = SchoolSetting::first();
        $principal = Teacher::with('user')->where('is_kepalasekolah', true)->first();
        $paperSize = $request->input('paper_size', 'a4');
        $orientation = $request->input('orientation', 'landscape');

        return view('reports.schedule', compact(
            'groupedSchedules', 'school', 'principal', 'rombels', 'studyGroupId',
            'showSubjectCode', 'showTeacherCode', 'paperSize', 'orientation'
        ));
    }
}
