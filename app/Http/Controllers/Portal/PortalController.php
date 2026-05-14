<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

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
        ], $data));
    }

    private function getStudentDashboardData($user): array
    {
        $student = $user->student;
        $studentId = $student?->id;

        // Stats Kehadiran Bulan Ini
        $stats = \App\Models\Attendance::where('student_id', $studentId)
            ->whereMonth('tanggal', now()->month)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return [
            'student' => $student,
            'studyGroup' => $student?->currentStudyGroup(),
            'attendance' => $student?->attendances()
                ->where('tanggal', now()->toDateString())
                ->first(),
            'attendanceStats' => array_merge(['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0], $stats),
            'recentGrades' => \App\Models\Grade::where('student_id', $studentId)
                ->with(['subject'])
                ->latest()
                ->take(3)
                ->get(),
        ];
    }

    private function getParentDashboardData($user): array
    {
        $childIds = $user->parent?->students()->pluck('id') ?? [];
        
        // Stats Kehadiran Bulan Ini (Semua Anak)
        $stats = \App\Models\Attendance::whereIn('student_id', $childIds)
            ->whereMonth('tanggal', now()->month)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return [
            'parent' => $user->parent,
            'children' => $user->parent?->students()->with(['user', 'attendances' => function($q) {
                $q->where('tanggal', now()->toDateString());
            }])->get(),
            'attendanceStats' => array_merge(['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0], $stats),
            'recentGrades' => \App\Models\Grade::whereIn('student_id', $childIds)
                ->with(['student.user', 'subject'])
                ->latest()
                ->take(3)
                ->get(),
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
