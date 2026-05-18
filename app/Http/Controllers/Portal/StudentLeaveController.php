<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentLeave;
use App\Models\SchoolSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentLeaveController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // If parent, show leaves for all their children
        if ($user->hasRole('wali')) {
            $parent = $user->parent;
            $leaves = StudentLeave::where('parent_id', $parent->id)
                ->with(['student.user', 'studyGroup'])
                ->latest()
                ->get();
            return view('portal.leaves.index', compact('leaves'));
        }

        // If student, show their own leaves
        if ($user->hasRole('siswa')) {
            $student = $user->student;
            $leaves = StudentLeave::where('student_id', $student->id)
                ->with(['studyGroup'])
                ->latest()
                ->get();
            return view('portal.leaves.index', compact('leaves'));
        }

        return redirect()->route('dashboard');
    }

    public function create()
    {
        $user = Auth::user();
        
        if (!$user->hasRole('wali')) {
            return redirect()->route('leaves.index')->with('error', 'Hanya orang tua yang dapat mengajukan izin.');
        }

        $parent = $user->parent;
        $students = $parent->students()->with('user')->get();

        return view('portal.leaves.create', compact('students'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'type' => 'required|in:sakit,izin',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10',
            'attachment' => 'nullable|image|max:2048',
        ]);

        $user = Auth::user();
        $parent = $user->parent;

        $student = Student::findOrFail($request->student_id);

        // Security check: Ensure student belongs to this parent
        if ($student->parent_id !== $parent->id) {
            return back()->with('error', 'Siswa tidak ditemukan dalam daftar anak Anda.');
        }

        $filePath = null;
        if ($request->hasFile('attachment')) {
            $filePath = $request->file('attachment')->store('student-leaves', 'public');
        }

        $currentRombel = $student->currentStudyGroup();

        $leave = StudentLeave::create([
            'student_id' => $request->student_id,
            'parent_id' => $parent->id,
            'study_group_id' => $currentRombel?->id,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'attachment' => $filePath,
            'status' => 'pending',
        ]);

        // Notify Walikelas via WA
        if ($currentRombel && $currentRombel->waliKelas && $currentRombel->waliKelas->no_whatsapp) {
            $teacher = $currentRombel->waliKelas;
            \Illuminate\Support\Facades\Log::info('WA Notify: Attempting to send to ' . $teacher->no_whatsapp);
            $schoolName = strtoupper(SchoolSetting::current()->name);
            $parentName = $user->name;
            $studentName = $student->user->name;
            $type = strtoupper($request->type);
            $dates = $request->start_date === $request->end_date 
                ? $request->start_date 
                : $request->start_date . ' s/d ' . $request->end_date;

            $message = "📢 *NOTIFIKASI IZIN SISWA - $schoolName*\n\n";
            $message .= "Yth. Bapak/Ibu *$teacher->kode_guru*,\n";
            $message .= "Pemberitahuan bahwa orang tua dari:\n\n";
            $message .= "👤 *Siswa:* $studentName\n";
            $message .= "📍 *Rombel:* $currentRombel->nama_rombel\n";
            $message .= "📝 *Jenis:* $type\n";
            $message .= "📅 *Waktu:* $dates\n";
            $message .= "💡 *Alasan:* $request->reason\n\n";
            $message .= "Mohon segera tinjau permohonan ini melalui Dashboard Aksara.\n";
            $message .= "--- _Powered by Aksara_ ---";

            \App\Services\WAService::sendMessageAsync($teacher->no_whatsapp, $message);
        } else {
            \Illuminate\Support\Facades\Log::warning('WA Notify: Conditions not met.', [
                'hasRombel' => (bool)$currentRombel,
                'hasWalikelas' => $currentRombel ? (bool)$currentRombel->waliKelas : false,
                'hasPhone' => ($currentRombel && $currentRombel->waliKelas) ? (bool)$currentRombel->waliKelas->no_whatsapp : false,
            ]);
        }

        return redirect()->route('leaves.index')->with('success', 'Permohonan izin berhasil dikirim.');
    }

    public function edit(StudentLeave $leave)
    {
        $user = Auth::user();
        if ($leave->parent_id !== $user->parent->id) {
            abort(403);
        }

        if ($leave->status !== 'rejected') {
            return redirect()->route('leaves.index')->with('error', 'Hanya permohonan yang ditolak yang dapat diubah.');
        }

        $students = $user->parent->students()->with('user')->get();

        return view('portal.leaves.edit', compact('leave', 'students'));
    }

    public function update(Request $request, StudentLeave $leave)
    {
        $user = Auth::user();
        if ($leave->parent_id !== $user->parent->id) {
            abort(403);
        }

        $request->validate([
            'student_id' => 'required|exists:students,id',
            'type' => 'required|in:sakit,izin',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10',
            'attachment' => 'nullable|image|max:2048',
        ]);

        $filePath = $leave->attachment;
        if ($request->hasFile('attachment')) {
            $filePath = $request->file('attachment')->store('student-leaves', 'public');
        }

        $student = Student::findOrFail($request->student_id);
        $currentRombel = $student->currentStudyGroup();

        $leave->update([
            'student_id' => $request->student_id,
            'study_group_id' => $currentRombel?->id,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'attachment' => $filePath,
            'status' => 'pending', // Reset to pending
            'rejection_note' => null, // Clear rejection note
        ]);

        // Re-notify Walikelas
        if ($currentRombel && $currentRombel->waliKelas && $currentRombel->waliKelas->no_whatsapp) {
            $teacher = $currentRombel->waliKelas;
            $schoolName = strtoupper(SchoolSetting::current()->name);
            $studentName = $student->user->name;

            $message = "📢 *REVISI IZIN SISWA - $schoolName*\n\n";
            $message .= "Yth. Bapak/Ibu *$teacher->kode_guru*,\n";
            $message .= "Orang tua telah mengirimkan REVISI permohonan izin untuk:\n\n";
            $message .= "👤 *Siswa:* $studentName\n";
            $message .= "📝 *Jenis:* " . strtoupper($request->type) . "\n";
            $message .= "💡 *Alasan Baru:* $request->reason\n\n";
            $message .= "Mohon tinjau kembali melalui Dashboard Aksara.\n";
            $message .= "--- _Powered by Aksara_ ---";

            \App\Services\WAService::sendMessageAsync($teacher->no_whatsapp, $message);
        }

        return redirect()->route('leaves.index')->with('success', 'Permohonan izin berhasil diperbarui.');
    }
}
