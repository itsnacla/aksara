<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Extracurricular;
use App\Models\Schedule;
use App\Models\StudentLeave;
use App\Models\AcademicYear;
use App\Models\StudentRapor;
use App\Models\Notification;
use Carbon\Carbon;

class PortalController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $data = [];

        if ($user->can('AccessStudentPortal')) {
            $data = array_merge($data, $this->getStudentDashboardData($user));
        }

        if ($user->can('AccessParentPortal')) {
            $data = array_merge($data, $this->getParentDashboardData($user));
        }

        return view('portal.dashboard', array_merge([
            'student' => null,
            'studyGroup' => null,
            'attendance' => null,
            'children' => collect(),
            'attendanceStats' => ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0],
            'recentGrades' => collect(),
            'extracurriculars' => collect(),
            'todaySchedules' => collect(),
            'attendanceTrend' => [],
            'gradeAverage' => 0,
            'totalSubjects' => 0,
            'recentNotifications' => collect(),
            'attendancePercentage' => 0,
            'academicYear' => null,
        ], $data));
    }

    /**
     * API endpoint for real-time polling data updates.
     */
    public function realtimeData(Request $request)
    {
        $user = Auth::user();
        $data = [];

        if ($user->can('AccessStudentPortal')) {
            $student = $user->student;
            $studentId = $student?->id;

            $todayAttendance = $student?->attendances()
                ->where('tanggal', now()->toDateString())
                ->first();

            $stats = Attendance::where('student_id', $studentId)
                ->whereMonth('tanggal', now()->month)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            $data = [
                'attendance' => $todayAttendance ? [
                    'status' => $todayAttendance->status,
                    'check_in' => $todayAttendance->check_in,
                    'time' => $todayAttendance->created_at->format('H:i'),
                ] : null,
                'attendanceStats' => array_merge(['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0], $stats),
                'notifications_count' => Notification::where('student_id', $studentId)
                    ->where('is_read', false)->count(),
            ];
        }

        if ($user->can('AccessParentPortal')) {
            $childIds = $user->parent?->students()->pluck('id') ?? [];

            $stats = Attendance::whereIn('student_id', $childIds)
                ->whereMonth('tanggal', now()->month)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            $childrenStatus = [];
            $children = $user->parent?->students()->with(['user', 'attendances' => function ($q) {
                $q->where('tanggal', now()->toDateString());
            }])->get() ?? collect();

            foreach ($children as $child) {
                $att = $child->attendances->first();
                $childrenStatus[] = [
                    'id' => $child->id,
                    'name' => $child->user->name,
                    'attendance' => $att ? [
                        'status' => $att->status,
                        'time' => $att->created_at->format('H:i'),
                    ] : null,
                ];
            }

            $data = array_merge($data, [
                'attendanceStats' => array_merge(['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0], $stats),
                'childrenStatus' => $childrenStatus,
                'notifications_count' => Notification::whereIn('student_id', $childIds)
                    ->where('is_read', false)->count(),
            ]);
        }

        return response()->json($data);
    }

    private function getStudentDashboardData($user): array
    {
        $student = $user->student;
        $studentId = $student?->id;
        $studyGroup = $student?->currentStudyGroup();
        $activeYear = AcademicYear::where('is_active', true)->first();

        // Stats Kehadiran Bulan Ini
        $stats = Attendance::where('student_id', $studentId)
            ->whereMonth('tanggal', now()->month)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Attendance trend (last 6 months)
        $attendanceTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthAttendances = Attendance::where('student_id', $studentId)
                ->whereYear('tanggal', $month->year)
                ->whereMonth('tanggal', $month->month)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            $total = array_sum($monthAttendances);
            $attendanceTrend[] = [
                'month' => $month->translatedFormat('M'),
                'hadir' => $monthAttendances['hadir'] ?? 0,
                'izin' => $monthAttendances['izin'] ?? 0,
                'sakit' => $monthAttendances['sakit'] ?? 0,
                'alpa' => $monthAttendances['alpa'] ?? 0,
                'percentage' => $total > 0 ? round((($monthAttendances['hadir'] ?? 0) / $total) * 100) : 0,
            ];
        }

        // Overall attendance percentage
        $totalAttendances = Attendance::where('student_id', $studentId)
            ->whereMonth('tanggal', now()->month)
            ->count();
        $totalHadir = $stats['hadir'] ?? 0;
        $attendancePercentage = $totalAttendances > 0 ? round(($totalHadir / $totalAttendances) * 100) : 0;

        // Grade Average
        $grades = Grade::where('student_id', $studentId)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->get();
        $gradeValues = $grades->map(function ($g) {
            $vals = array_filter([$g->nilai_tugas, $g->nilai_uts, $g->nilai_uas]);
            return count($vals) > 0 ? array_sum($vals) / count($vals) : null;
        })->filter();
        $gradeAverage = $gradeValues->count() > 0 ? round($gradeValues->avg(), 1) : 0;

        // Today's schedule
        $dayMap = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
        $today = $dayMap[now()->format('l')] ?? now()->format('l');
        $todaySchedules = $studyGroup
            ? Schedule::where('study_group_id', $studyGroup->id)
                ->where('hari', $today)
                ->with(['subject', 'teacher.user', 'startTimeSlot', 'endTimeSlot'])
                ->orderBy('start_time_slot_id')
                ->get()
            : collect();

        // Recent Notifications
        $recentNotifications = Notification::where('student_id', $studentId)
            ->latest()
            ->take(5)
            ->get();

        return [
            'student' => $student,
            'studyGroup' => $studyGroup,
            'attendance' => $student?->attendances()
                ->where('tanggal', now()->toDateString())
                ->first(),
            'attendanceStats' => array_merge(['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0], $stats),
            'recentGrades' => Grade::where('student_id', $studentId)
                ->with(['subject'])
                ->latest()
                ->take(5)
                ->get(),
            'extracurriculars' => Extracurricular::orderBy('kategori', 'asc')
                ->orderBy('nama_ekskul', 'asc')
                ->get(),
            'todaySchedules' => $todaySchedules,
            'attendanceTrend' => $attendanceTrend,
            'gradeAverage' => $gradeAverage,
            'totalSubjects' => $grades->unique('subject_id')->count(),
            'attendancePercentage' => $attendancePercentage,
            'recentNotifications' => $recentNotifications,
            'academicYear' => $activeYear,
        ];
    }

    private function getParentDashboardData($user): array
    {
        $childIds = $user->parent?->students()->pluck('id') ?? [];
        $activeYear = AcademicYear::where('is_active', true)->first();

        // Stats Kehadiran Bulan Ini (Semua Anak)
        $stats = Attendance::whereIn('student_id', $childIds)
            ->whereMonth('tanggal', now()->month)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Attendance Trend (last 6 months, all children)
        $attendanceTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthAttendances = Attendance::whereIn('student_id', $childIds)
                ->whereYear('tanggal', $month->year)
                ->whereMonth('tanggal', $month->month)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            $total = array_sum($monthAttendances);
            $attendanceTrend[] = [
                'month' => $month->translatedFormat('M'),
                'hadir' => $monthAttendances['hadir'] ?? 0,
                'izin' => $monthAttendances['izin'] ?? 0,
                'sakit' => $monthAttendances['sakit'] ?? 0,
                'alpa' => $monthAttendances['alpa'] ?? 0,
                'percentage' => $total > 0 ? round((($monthAttendances['hadir'] ?? 0) / $total) * 100) : 0,
            ];
        }

        // Overall attendance percentage
        $totalAttendances = Attendance::whereIn('student_id', $childIds)
            ->whereMonth('tanggal', now()->month)
            ->count();
        $totalHadir = $stats['hadir'] ?? 0;
        $attendancePercentage = $totalAttendances > 0 ? round(($totalHadir / $totalAttendances) * 100) : 0;

        // Grade Average for all children
        $grades = Grade::whereIn('student_id', $childIds)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->get();
        $gradeValues = $grades->map(function ($g) {
            $vals = array_filter([$g->nilai_tugas, $g->nilai_uts, $g->nilai_uas]);
            return count($vals) > 0 ? array_sum($vals) / count($vals) : null;
        })->filter();
        $gradeAverage = $gradeValues->count() > 0 ? round($gradeValues->avg(), 1) : 0;

        // Recent Notifications for all children
        $recentNotifications = Notification::whereIn('student_id', $childIds)
            ->with('student.user')
            ->latest()
            ->take(5)
            ->get();

        return [
            'parent' => $user->parent,
            'children' => $user->parent?->students()->with(['user', 'attendances' => function($q) {
                $q->where('tanggal', now()->toDateString());
            }])->get() ?? collect(),
            'attendanceStats' => array_merge(['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0], $stats),
            'recentGrades' => Grade::whereIn('student_id', $childIds)
                ->with(['student.user', 'subject'])
                ->latest()
                ->take(5)
                ->get(),
            'extracurriculars' => Extracurricular::orderBy('kategori', 'asc')
                ->orderBy('nama_ekskul', 'asc')
                ->get(),
            'attendanceTrend' => $attendanceTrend,
            'gradeAverage' => $gradeAverage,
            'totalSubjects' => $grades->unique('subject_id')->count(),
            'attendancePercentage' => $attendancePercentage,
            'recentNotifications' => $recentNotifications,
            'academicYear' => $activeYear,
        ];
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
