<?php

namespace App\Ai\Tools;

use App\Models\StudyGroup;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ClusterStudents implements Tool
{
    public function __construct(protected ?User $user = null) {}

    public function description(): Stringable|string
    {
        return 'Mengelompokkan (clustering) karakteristik belajar seluruh siswa di suatu kelas (Rombel) berdasarkan rata-rata nilai mata pelajaran mereka.';
    }

    public function handle(Request $request): Stringable|string
    {
        if (! $this->user) {
            return 'Error: User context missing.';
        }

        $className = $request['class_name'] ?? null;
        if (! $className) {
            return 'Error: class_name is required.';
        }

        $roleName = $this->user->roles->first()?->name ?? 'siswa';
        if (! in_array(strtolower($roleName), ['admin', 'super_admin', 'guru', 'staff'])) {
            return 'Akses ditolak. Fitur Clustering hanya untuk Guru dan Admin.';
        }

        $studyGroup = StudyGroup::with(['students.grades.subject', 'students.user'])
            ->where('nama_rombel', 'like', '%'.$className.'%')
            ->first();
        if (! $studyGroup) {
            return 'Kelas/Rombel dengan nama '.$className.' tidak ditemukan.';
        }

        $studyGroupId = $studyGroup->id;

        // Validasi akses jika Guru
        if (str_contains(strtolower($roleName), 'guru') && $this->user->teacher) {
            $rombelIds = $this->user->teacher->studyGroups()->pluck('id')->toArray();
            if (! in_array($studyGroupId, $rombelIds)) {
                return 'Akses ditolak. Kelas ini bukan kelas perwalian Anda.';
            }
        }

        $studentData = [];
        foreach ($studyGroup->students as $student) {
            $subjectGrades = [];
            foreach ($student->grades as $grade) {
                $mapel = $grade->subject->nama_mapel ?? 'Lainnya';
                if (! isset($subjectGrades[$mapel])) {
                    $subjectGrades[$mapel] = [];
                }
                $subjectGrades[$mapel][] = ($grade->nilai_tugas + $grade->nilai_uts + $grade->nilai_uas) / 3;
            }

            $averages = [];
            foreach ($subjectGrades as $mapel => $scores) {
                if (count($scores) > 0) {
                    $averages[$mapel] = round(array_sum($scores) / count($scores), 1);
                }
            }

            $studentData[] = [
                'nama' => $student->user->name,
                'rata_rata_mapel' => $averages,
            ];
        }

        return json_encode([
            'class_name' => $studyGroup->nama_rombel,
            'students' => $studentData,
            'instruction_to_ai' => 'Tugas Anda: Lakukan clustering terhadap siswa-siswa ini berdasarkan pola nilai mereka. Buat kategori gaya belajar (misal: Logika Kuat, Seni/Bahasa, dll), masukkan nama siswa ke dalam kategori tersebut, dan beri rekomendasi pedagogis untuk guru.',
        ], JSON_PRETTY_PRINT);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'class_name' => $schema->string()->description('Nama Rombongan Belajar / Kelas yang akan dicluster (misal: X IPA 1)'),
        ];
    }
}
