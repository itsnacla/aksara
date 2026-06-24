<?php

namespace App\Ai\Tools;

use App\Models\Grade;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class AnalyzeDropoutRisk implements Tool
{
    public function __construct(protected ?User $user = null) {}

    public function description(): Stringable|string
    {
        return 'Menganalisis data akademik siswa secara mendalam untuk memprediksi risiko dropout atau tinggal kelas (Early Warning System). Gunakan ini jika diminta memprediksi risiko siswa.';
    }

    public function handle(Request $request): Stringable|string
    {
        if (!$this->user) return 'Error: User context missing.';

        $studentName = $request['student_name'] ?? null;
        if (!$studentName) return 'Error: student_name is required.';

        $roleName = $this->user->roles->first()?->name ?? 'siswa';
        if (!in_array(strtolower($roleName), ['admin', 'super_admin', 'guru', 'staff'])) {
            return 'Akses ditolak. Fitur Early Warning System hanya untuk Guru dan Admin.';
        }

        $student = \App\Models\Student::with('user')->whereHas('user', function($q) use ($studentName) {
            $q->where('name', 'like', '%' . $studentName . '%');
        })->first();
        if (!$student) return 'Siswa dengan nama ' . $studentName . ' tidak ditemukan di database.';

        if (!$this->hasAccessToStudent(strtolower($roleName), $student)) {
            return 'Akses ditolak. Siswa ini tidak berada di kelas perwalian Anda.';
        }

        return $this->buildAnalysisData($student);
    }

    private function hasAccessToStudent(string $roleName, \App\Models\Student $student): bool
    {
        if (str_contains($roleName, 'guru') && $this->user->teacher) {
            $rombelIds = $this->user->teacher->studyGroups()->pluck('id')->toArray();
            $studentRombs = $student->studyGroups()->pluck('study_groups.id')->toArray();
            if (empty(array_intersect($rombelIds, $studentRombs))) {
                return false;
            }
        }
        return true;
    }

    private function buildAnalysisData(\App\Models\Student $student): string
    {
        $studentId = $student->id;
        $grades = Grade::with('subject')->where('student_id', $studentId)->latest()->take(50)->get()->map(fn($g) => [
            'mapel' => $g->subject->nama_mapel ?? 'N/A',
            'tugas' => $g->nilai_tugas,
            'uts' => $g->nilai_uts,
            'uas' => $g->nilai_uas,
        ]);

        $attendance = Attendance::where('student_id', $studentId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->get();

        return json_encode([
            'student_name' => $student->user->name,
            'grades_history' => $grades,
            'attendance_summary' => $attendance,
            'instruction_to_ai' => 'Tugas Anda: Bertindak sebagai Data Scientist. Analisis data di atas dan berikan prediksi persentase risiko dropout/tinggal kelas (Rendah/Menengah/Tinggi) serta rekomendasi pencegahannya dalam format markdown yang rapi. Gunakan bahasa Indonesia.'
        ], JSON_PRETTY_PRINT);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'student_name' => $schema->string()->description('Nama siswa (atau potongan nama) yang akan dianalisis risikonya'),
        ];
    }
}
