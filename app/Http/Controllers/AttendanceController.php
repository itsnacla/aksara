<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Student;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    // Halaman scan QR
    public function scanPage()
    {
        $schedules = Schedule::with(['schoolClass', 'subject'])->get();
        return view('attendance.scan', compact('schedules'));
    }

    // Proses hasil scan QR
    public function processQr(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
            'schedule_id' => 'nullable|exists:schedules,id',
        ]);

        $student = Student::where('qr_code', $request->qr_code)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found. Invalid QR Code.',
            ], 404);
        }

        // Cek apakah sudah absen hari ini untuk jadwal yang sama
        $existing = Attendance::where('student_id', $student->id)
            ->where('schedule_id', $request->schedule_id)
            ->whereDate('tanggal', today())
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => $student->nama_siswa . ' already marked Present today.',
                'student' => $student->nama_siswa,
            ]);
        }

        // Simpan attendance
        Attendance::create([
            'student_id' => $student->id,
            'schedule_id' => $request->schedule_id,
            'tanggal' => today(),
            'status' => 'Present',
        ]);

        return response()->json([
            'success' => true,
            'message' => '✅ ' . $student->nama_siswa . ' marked as Present!',
            'student' => $student->nama_siswa,
            'class' => $student->schoolClass?->nama_kelas,
        ]);
    }

    // Tampilkan QR Card siswa
    public function showCard(Student $student)
    {
        return view('attendance.qr-card', compact('student'));
    }
}