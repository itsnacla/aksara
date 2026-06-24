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
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function realtimeData(Request $request)
    {
        $user = Auth::user();
        $data = [];

        if ($user->can('AccessStudentPortal')) {
            $data = $this->getStudentRealtimeData($user);
        }

        if ($user->can('AccessParentPortal')) {
            $data = array_merge($data, $this->getParentRealtimeData($user));
        }

        return response()->json($data);
    }

    /**
     * @param \App\Models\User $user
     * @return array<string, mixed>
     */
    private function getStudentRealtimeData($user): array
    {
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

        return [
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

    /**
     * @param \App\Models\User $user
     * @return array<string, mixed>
     */
    private function getParentRealtimeData($user): array
    {
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

        return [
            'attendanceStats' => array_merge(['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0], $stats),
            'childrenStatus' => $childrenStatus,
            'notifications_count' => Notification::whereIn('student_id', $childIds)
                ->where('is_read', false)->count(),
        ];
    }

    /**
     * @param \App\Models\User $user
     * @return array<string, mixed>
     */
    private function getStudentDashboardData($user): array
    {
        $studentId = $user->student?->id;
        $activeYear = AcademicYear::where('is_active', true)->first();
        $cacheKey = "student_dashboard_{$studentId}_" . ($activeYear ? $activeYear->id : '0');

        /** @var array<string, mixed> $result */
        $result = \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, fn () => $this->buildStudentDashboardArray($user, $activeYear));

        return $result;
    }

    private function buildStudentDashboardArray($user, $activeYear): array
    {
        $studentId = $user->student?->id;
        $stats = $this->getStudentAttendanceStats($studentId);
        $grades = $this->getStudentGrades($studentId, $activeYear);

        return array_merge(
            $this->buildStudentDashboardPart1($user, $activeYear, $stats, $grades),
            $this->buildStudentDashboardPart2($user, $activeYear)
        );
    }

    private function buildStudentDashboardPart1($user, $activeYear, $stats, $grades): array
    {
        $student = $user->student;
        $studentId = $student?->id;
        
        return [
            'student' => $student,
            'studyGroup' => $student?->currentStudyGroup(),
            'attendanceStats' => array_merge(['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0], $stats),
            'attendanceTrend' => $this->getStudentAttendanceTrend($studentId),
            'attendancePercentage' => $this->getStudentAttendancePercentage($studentId, $stats),
            'gradeAverage' => $this->calculateGradeAverage($grades),
            'totalSubjects' => $grades->unique('subject_id')->count(),
            'recentGrades' => $this->getStudentRecentGrades($studentId),
        ];
    }

    private function buildStudentDashboardPart2($user, $activeYear): array
    {
        $student = $user->student;
        $studentId = $student?->id;
        $studyGroup = $student?->currentStudyGroup();

        return [
            'todaySchedules' => $this->getStudentTodaySchedules($studyGroup),
            'recentNotifications' => $this->getStudentRecentNotifications($studentId),
            'publishedRapors' => $this->getStudentPublishedRapors($studentId),
            'extracurriculars' => $student ? $student->extracurriculars()->with(['coordinator.teacher'])->orderBy('kategori', 'asc')->orderBy('nama_ekskul', 'asc')->get() : collect(),
            'attendance' => $student?->attendances()->where('tanggal', now()->toDateString())->first(),
            'recentLeaves' => StudentLeave::where('student_id', $studentId)->with(['student.user'])->latest()->take(3)->get(),
            'p5Projects' => $student && $activeYear ? \App\Models\P5Group::whereHas('students', function ($q) use ($student) {
                $q->where('p5_group_student.student_id', $student->id);
            })->where('academic_year_id', $activeYear->id)->with('project.theme')->get() : collect(),
            'academicYear' => $activeYear,
        ];
    }

    private function getStudentAttendancePercentage(?int $studentId, array $stats): int
    {
        $totalAttendances = Attendance::where('student_id', $studentId)
            ->whereMonth('tanggal', now()->month)
            ->count();
        $totalHadir = $stats['hadir'] ?? 0;
        return $totalAttendances > 0 ? (int) round(($totalHadir / $totalAttendances) * 100) : 0;
    }

    private function getStudentGrades(?int $studentId, $activeYear)
    {
        return Grade::where('student_id', $studentId)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                      ->from('student_rapors')
                      ->whereColumn('student_rapors.student_id', 'grades.student_id')
                      ->whereColumn('student_rapors.academic_year_id', 'grades.academic_year_id')
                      ->where('student_rapors.is_published', true);
            })
            ->get(['nilai_tugas', 'nilai_uts', 'nilai_uas', 'subject_id']);
    }

    private function getStudentAttendanceStats(?int $studentId): array
    {
        return Attendance::where('student_id', $studentId)
            ->whereMonth('tanggal', now()->month)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }

    private function getStudentRecentNotifications(?int $studentId)
    {
        return Notification::where('student_id', $studentId)
            ->latest()
            ->take(5)
            ->get();
    }

    private function getStudentPublishedRapors(?int $studentId)
    {
        return StudentRapor::with(['academicYear'])
            ->where('student_id', $studentId)
            ->where('is_published', true)
            ->latest()
            ->get();
    }

    private function getStudentRecentGrades(?int $studentId)
    {
        return Grade::where('student_id', $studentId)
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                      ->from('student_rapors')
                      ->whereColumn('student_rapors.student_id', 'grades.student_id')
                      ->whereColumn('student_rapors.academic_year_id', 'grades.academic_year_id')
                      ->where('student_rapors.is_published', true);
            })
            ->with(['subject'])
            ->latest()
            ->take(5)
            ->get();
    }

    private function calculateGradeAverage(\Illuminate\Database\Eloquent\Collection $grades): float
    {
        $gradeValues = $grades->map(function ($g) {
            $vals = array_filter([$g->nilai_tugas, $g->nilai_uts, $g->nilai_uas]);
            return count($vals) > 0 ? array_sum($vals) / count($vals) : null;
        })->filter();
        return $gradeValues->count() > 0 ? round($gradeValues->avg(), 1) : 0;
    }

    private function getStudentAttendanceTrend(?int $studentId): array
    {
        return \Illuminate\Support\Facades\Cache::remember("attendance_trend_{$studentId}", 3600, function () use ($studentId) {
            $trend = [];
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
                $trend[] = [
                    'month' => $month->translatedFormat('M'),
                    'hadir' => $monthAttendances['hadir'] ?? 0,
                    'izin' => $monthAttendances['izin'] ?? 0,
                    'sakit' => $monthAttendances['sakit'] ?? 0,
                    'alpa' => $monthAttendances['alpa'] ?? 0,
                    'percentage' => $total > 0 ? round((($monthAttendances['hadir'] ?? 0) / $total) * 100) : 0,
                ];
            }
            return $trend;
        });
    }

    private function getStudentTodaySchedules($studyGroup)
    {
        $dayMap = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
        $today = $dayMap[now()->format('l')] ?? now()->format('l');
        return $studyGroup
            ? Schedule::where('study_group_id', $studyGroup->id)
                ->where('hari', $today)
                ->with(['subject', 'teacher.user', 'startTimeSlot', 'endTimeSlot'])
                ->orderBy('start_time_slot_id')
                ->get()
            : collect();
    }

    /**
     * @param \App\Models\User $user
     * @return array<string, mixed>
     */
    private function getParentDashboardData($user): array
    {
        $childIds = $user->parent?->students()->pluck('id')->toArray() ?? [];
        $activeYear = AcademicYear::where('is_active', true)->first();
        
        $cacheKey = "parent_dashboard_" . implode('_', $childIds) . "_" . ($activeYear ? $activeYear->id : '0');

        /** @var array<string, mixed> $result */
        $result = \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, fn () => $this->buildParentDashboardArray($user, $childIds, $activeYear));

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildParentDashboardArray($user, $childIds, $activeYear): array
    {
        $data = new \stdClass();
        $data->parent = $user->parent;
        
        $stats = $this->getParentAttendanceStats($childIds);
        $data->attendanceStats = array_merge(['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0], $stats);
        $data->attendanceTrend = $this->getParentAttendanceTrend($childIds);
        $data->attendancePercentage = $this->getParentAttendancePercentage($childIds, $stats);
        
        $grades = $this->getParentGrades($childIds, $activeYear);
        $data->gradeAverage = $this->calculateGradeAverage($grades);
        $data->totalSubjects = $grades->unique('subject_id')->count();
        $data->recentGrades = $this->getParentRecentGrades($childIds);

        $data->recentNotifications = $this->getParentRecentNotifications($childIds);
        $data->publishedRapors = $this->getParentPublishedRapors($childIds);

        $data->children = $data->parent?->students()->with(['user', 'attendances' => function($q) {
            $q->where('tanggal', now()->toDateString());
        }])->get() ?? collect();

        $data->extracurriculars = Extracurricular::whereHas('students', function ($q) use ($childIds) {
            $q->whereIn('extracurricular_student.student_id', $childIds);
        })->with(['coordinator.teacher'])->orderBy('kategori', 'asc')->orderBy('nama_ekskul', 'asc')->get();

        $data->recentLeaves = StudentLeave::whereIn('student_id', $childIds)
            ->with(['student.user'])
            ->latest()
            ->take(3)
            ->get();

        $data->p5Projects = collect(); // Parents might not see this directly in dashboard yet
        $data->academicYear = $activeYear;

        return (array) $data;
    }

    private function getParentAttendancePercentage(array $childIds, array $stats): int
    {
        $totalAttendances = Attendance::whereIn('student_id', $childIds)
            ->whereMonth('tanggal', now()->month)
            ->count();
        $totalHadir = $stats['hadir'] ?? 0;
        return $totalAttendances > 0 ? (int) round(($totalHadir / $totalAttendances) * 100) : 0;
    }

    private function getParentGrades(array $childIds, $activeYear)
    {
        return Grade::whereIn('student_id', $childIds)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                      ->from('student_rapors')
                      ->whereColumn('student_rapors.student_id', 'grades.student_id')
                      ->whereColumn('student_rapors.academic_year_id', 'grades.academic_year_id')
                      ->where('student_rapors.is_published', true);
            })
            ->get(['nilai_tugas', 'nilai_uts', 'nilai_uas', 'subject_id']);
    }

    private function getParentAttendanceStats(array $childIds): array
    {
        return Attendance::whereIn('student_id', $childIds)
            ->whereMonth('tanggal', now()->month)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }

    private function getParentRecentNotifications(array $childIds)
    {
        return Notification::whereIn('student_id', $childIds)
            ->with('student.user')
            ->latest()
            ->take(5)
            ->get();
    }

    private function getParentPublishedRapors(array $childIds)
    {
        return StudentRapor::whereIn('student_id', $childIds)
            ->where('is_published', true)
            ->with(['student.user', 'academicYear'])
            ->latest()
            ->get();
    }

    private function getParentRecentGrades(array $childIds)
    {
        return Grade::whereIn('student_id', $childIds)
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                      ->from('student_rapors')
                      ->whereColumn('student_rapors.student_id', 'grades.student_id')
                      ->whereColumn('student_rapors.academic_year_id', 'grades.academic_year_id')
                      ->where('student_rapors.is_published', true);
            })
            ->with(['student.user', 'subject'])
            ->latest()
            ->take(5)
            ->get();
    }

    private function getExtracurricularsList()
    {
        return \Illuminate\Support\Facades\Cache::remember('extracurriculars_list', 3600, function() {
            return Extracurricular::with(['coordinator.teacher'])
                ->orderBy('kategori', 'asc')
                ->orderBy('nama_ekskul', 'asc')
                ->get();
        });
    }

    private function getParentAttendanceTrend(array $childIds): array
    {
        return \Illuminate\Support\Facades\Cache::remember("attendance_trend_parent_" . implode('_', $childIds), 3600, function () use ($childIds) {
            $trend = [];
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
                $trend[] = [
                    'month' => $month->translatedFormat('M'),
                    'hadir' => $monthAttendances['hadir'] ?? 0,
                    'izin' => $monthAttendances['izin'] ?? 0,
                    'sakit' => $monthAttendances['sakit'] ?? 0,
                    'alpa' => $monthAttendances['alpa'] ?? 0,
                    'percentage' => $total > 0 ? round((($monthAttendances['hadir'] ?? 0) / $total) * 100) : 0,
                ];
            }
            return $trend;
        });
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
