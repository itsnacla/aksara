<?php

namespace App\Services\Academic;

use App\Ai\Agents\WaliKelasAgent;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\ChatbotSetting;
use App\Models\Cocurricular;
use App\Models\Extracurricular;
use App\Models\ExtracurricularGrade;
use App\Models\Grade;
use App\Models\LearningObjective;
use App\Models\Level;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\StudentRapor;
use App\Models\StudyGroup;
use App\Models\SubjectReportMapping;
use App\Models\Teacher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class RaporService
{
    private function getGroupedSubjects(array $subjectsData): Collection
    {
        return collect($subjectsData)
            ->groupBy('group')
            ->sortBy(function ($subjects, $groupName) {
                if (stripos((string) $groupName, 'Kelompok A') !== false || stripos((string) $groupName, 'Umum') !== false) {
                    return 1;
                }
                if (stripos((string) $groupName, 'Kelompok B') !== false || stripos((string) $groupName, 'Muatan Lokal') !== false) {
                    return 2;
                }

                return 3;
            });
    }

    private function getPersistedOrGeneratedData(Student $student, int $academicYearId, ?StudyGroup $rombel): array
    {
        $persistedRapor = StudentRapor::where('student_id', $student->id)
            ->where('academic_year_id', $academicYearId)
            ->first();

        if ($persistedRapor) {
            return [
                'sakit' => $persistedRapor->sakit,
                'izin' => $persistedRapor->izin,
                'alpha' => $persistedRapor->alpha,
                'catatanWalikelas' => $persistedRapor->catatan_wali_kelas,
                'isNaik' => $persistedRapor->is_naik,
                'kenaikanKelasTo' => $persistedRapor->kenaikan_kelas_to,
                'isGenerated' => true,
            ];
        }

        $attendance = $this->getAttendanceRecap($student, $academicYearId);
        $rank = $this->getStudentRank($student, $academicYearId, $rombel);

        return [
            'sakit' => $attendance['sakit'],
            'izin' => $attendance['izin'],
            'alpha' => $attendance['alpha'],
            'catatanWalikelas' => "Selamat {$student->user->name}, kamu berhasil meraih peringkat ke-{$rank}. Pertahankan semangat belajarmu dan jangan cepat puas nak!",
            'isNaik' => null,
            'kenaikanKelasTo' => null,
            'isGenerated' => false,
        ];
    }

    /**
     * Get all data required to render the student's academic report card.
     *
     * @return array<string, mixed>
     */
    public function getStudentRaporData(Student $student, int $academicYearId): array
    {
        // Simpler eager loading without nested Closures to keep static analysis complexity minimal
        $student->load([
            'user',
            'parent',
            'studyGroups.level.subjects.subjectReportGroup',
            'studyGroups.waliKelas.user',
        ]);

        /** @var StudyGroup|null $rombel */
        $rombel = $student->studyGroups->where('academic_year_id', $academicYearId)->first();

        /** @var Level|null $level */
        $level = $rombel?->level;

        /** @var SchoolSetting $school */
        $school = SchoolSetting::current();

        /** @var Teacher|null $principal */
        $principal = Teacher::with('user')->where('is_kepalasekolah', true)->first();

        /** @var AcademicYear|null $activeYear */
        $activeYear = AcademicYear::find($academicYearId);

        $subjectsData = $this->getSubjectsData($student, $academicYearId, $level);
        $groupedSubjects = $this->getGroupedSubjects($subjectsData);

        $p5Data = $this->getP5ProjectAndProfiles($student, $academicYearId);

        $attendanceData = $this->getPersistedOrGeneratedData($student, $academicYearId, $rombel);

        /** @var array<string, mixed> $result */
        $result = [];
        $result['student'] = $student;
        $result['school'] = $school;
        $result['principal'] = $principal;
        $result['activeYear'] = $activeYear;
        $result['rombel'] = $rombel;
        $result['groupedSubjects'] = $groupedSubjects;
        $result['sakit'] = $attendanceData['sakit'];
        $result['izin'] = $attendanceData['izin'];
        $result['alpha'] = $attendanceData['alpha'];
        $result['ekskuls'] = $this->getEkskulsRecap($student, $academicYearId);
        $result['cocurriculars'] = $this->getCocurricularData($level, $activeYear);
        $result['p5Project'] = $p5Data['p5Project'];
        $result['graduateProfiles'] = $p5Data['graduateProfiles'];
        $result['rank'] = $this->getStudentRank($student, $academicYearId, $rombel);
        $result['catatanWalikelas'] = $attendanceData['catatanWalikelas'];
        $result['isNaik'] = $attendanceData['isNaik'];
        $result['kenaikanKelasTo'] = $attendanceData['kenaikanKelasTo'];
        $result['isGenerated'] = $attendanceData['isGenerated'];

        return $result;
    }

    /**
     * Get cocurricular projects based on the active level and year.
     */
    private function getCocurricularData(?Level $level, ?AcademicYear $activeYear): array
    {
        if (! $level) {
            return [];
        }

        return Cocurricular::where('fase', $level->fase)
            ->where('tahun_ajaran', $activeYear?->tahun_ajaran)
            ->get()
            ->toArray();
    }

    /**
     * Get attendance counts for sakit, izin, and alpha.
     */
    private function getAttendanceRecap(Student $student, int $academicYearId): array
    {
        return [
            'sakit' => $this->getAttendanceCount($student->id, $academicYearId, 'sakit'),
            'izin' => $this->getAttendanceCount($student->id, $academicYearId, 'izin'),
            'alpha' => $this->getAttendanceCount($student->id, $academicYearId, 'alpha'),
        ];
    }

    /**
     * Get P5 project and its parsed graduate profiles.
     */
    private function getP5ProjectAndProfiles(Student $student, int $academicYearId): array
    {
        $p5Group = $student->p5Groups()
            ->whereHas('project', function ($q) use ($academicYearId) {
                $q->where('p5_projects.academic_year_id', $academicYearId);
            })
            ->first();

        $p5Project = $p5Group?->project;
        $graduateProfiles = [];

        if ($p5Project && is_array($p5Project->graduate_profile)) {
            foreach ($p5Project->graduate_profile as $profileString) {
                $parts = explode(': ', $profileString);
                if (count($parts) === 2) {
                    $dimensi = trim($parts[0]);
                    $subdimensi = trim($parts[1]);
                    $graduateProfiles[$dimensi][] = $subdimensi;
                }
            }
        }

        return [
            'p5Project' => $p5Project,
            'graduateProfiles' => $graduateProfiles,
        ];
    }

    /**
     * Compile grade and learning objectives details for each subject associated with the student's level.
     */
    private function getSubjectsData(Student $student, int $academicYearId, ?Level $level): array
    {
        $subjectsData = [];
        if (! $level) {
            return $subjectsData;
        }

        // Pre-fetch all grades for the student and academic year in one query
        $grades = Grade::where('student_id', $student->id)
            ->where('academic_year_id', $academicYearId)
            ->get()
            ->keyBy('subject_id');

        $mappings = SubjectReportMapping::with('subject.subjectReportGroup')
            ->whereJsonContains('level_ids', $level->id)
            ->orderBy('no_urut')
            ->get();

        if ($mappings->isNotEmpty()) {
            foreach ($mappings as $mapping) {
                $subject = $mapping->subject;
                if (! $subject) {
                    continue;
                }

                // Skip non-graded subjects
                if (! $subject->is_graded) {
                    continue;
                }

                $grade = $grades->get($subject->id);

                // Sembunyikan mata pelajaran Agama yang tidak ada nilainya (berarti bukan agama siswa tersebut)
                if (! $grade && str_contains(strtolower($subject->nama_mapel), 'pendidikan agama')) {
                    continue;
                }

                $groupName = $subject->subjectReportGroup?->nama_kelompok;
                if (! $groupName) {
                    $groupName = $subject->is_umum ? 'Kelompok A' : 'Kelompok B';
                }

                $subjectsData[] = [
                    'nama' => $subject->nama_mapel,
                    'no_urut' => $mapping->no_urut,
                    'group' => $groupName,
                    'nilai' => $grade ? round(($grade->nilai_tugas + $grade->nilai_uts + $grade->nilai_uas) / 3) : null,
                    'optimal_tp_ids' => $grade?->optimal_tp_ids ?? [],
                    'improved_tp_ids' => $grade?->improved_tp_ids ?? [],
                ];
            }
        } else {
            $subjects = $level->subjects()->with('subjectReportGroup')->get();
            foreach ($subjects as $index => $subject) {
                // Skip non-graded subjects
                if (! $subject->is_graded) {
                    continue;
                }

                $grade = $grades->get($subject->id);

                // Sembunyikan mata pelajaran Agama yang tidak ada nilainya (berarti bukan agama siswa tersebut)
                if (! $grade && str_contains(strtolower($subject->nama_mapel), 'pendidikan agama')) {
                    continue;
                }

                $groupName = $subject->subjectReportGroup?->nama_kelompok;
                if (! $groupName) {
                    $groupName = $subject->is_umum ? 'Kelompok A' : 'Kelompok B';
                }

                $subjectsData[] = [
                    'nama' => $subject->nama_mapel,
                    'no_urut' => $index + 1,
                    'group' => $groupName,
                    'nilai' => $grade ? round(($grade->nilai_tugas + $grade->nilai_uts + $grade->nilai_uas) / 3) : null,
                    'optimal_tp_ids' => $grade?->optimal_tp_ids ?? [],
                    'improved_tp_ids' => $grade?->improved_tp_ids ?? [],
                ];
            }
        }

        // Collect all learning objective IDs to fetch descriptions in one query
        $allTpIds = [];
        foreach ($subjectsData as $sub) {
            if (! empty($sub['optimal_tp_ids'])) {
                $allTpIds = array_merge($allTpIds, $sub['optimal_tp_ids']);
            }
            if (! empty($sub['improved_tp_ids'])) {
                $allTpIds = array_merge($allTpIds, $sub['improved_tp_ids']);
            }
        }
        $allTpIds = array_unique(array_filter($allTpIds));

        $tpsMap = [];
        if (! empty($allTpIds)) {
            $tpsMap = LearningObjective::whereIn('id', $allTpIds)
                ->pluck('description', 'id')
                ->toArray();
        }

        foreach ($subjectsData as &$sub) {
            $optimalDesc = '';
            $improvedDesc = '';

            if (! empty($sub['optimal_tp_ids'])) {
                $tps = [];
                foreach ($sub['optimal_tp_ids'] as $id) {
                    if (isset($tpsMap[$id])) {
                        $tps[] = $tpsMap[$id];
                    }
                }
                if (! empty($tps)) {
                    $optimalDesc = 'Menunjukkan penguasaan yang sangat baik dalam '.implode(', ', $tps).'.';
                }
            }

            if (! empty($sub['improved_tp_ids'])) {
                $tps = [];
                foreach ($sub['improved_tp_ids'] as $id) {
                    if (isset($tpsMap[$id])) {
                        $tps[] = $tpsMap[$id];
                    }
                }
                if (! empty($tps)) {
                    $improvedDesc = 'Perlu bimbingan dalam '.implode(', ', $tps).'.';
                }
            }

            $sub['deskripsi'] = trim($optimalDesc.' '.$improvedDesc) ?: 'Menunjukkan perkembangan kompetensi yang baik dan sesuai dengan kriteria ketuntasan.';
        }
        unset($sub);

        return $subjectsData;
    }

    /**
     * Count attendance days for a specific student, academic year, and status.
     */
    private function getAttendanceCount(int $studentId, int $academicYearId, string $status): int
    {
        return Attendance::where('student_id', $studentId)
            ->whereHas('studyGroup', fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->where('status', $status)
            ->count();
    }

    /**
     * Retrieve extracurricular grades for a specific student and academic year.
     */
    private function getEkskulsRecap(Student $student, int $academicYearId): array
    {
        return ExtracurricularGrade::where('student_id', $student->id)
            ->where('academic_year_id', $academicYearId)
            ->with('extracurricular')
            ->get()
            ->map(function ($grade) {
                $defaultKeterangan = ExtracurricularGrade::$defaultKeterangan[$grade->predikat] ?? 'Berpartisipasi aktif dan menunjukkan minat tinggi.';

                return [
                    'nama' => $grade->extracurricular->nama_ekskul,
                    'nilai' => $grade->predikat,
                    'deskripsi' => $grade->keterangan ?: $defaultKeterangan,
                ];
            })
            ->toArray();
    }

    /**
     * Calculate student ranking within their study group (rombel).
     */
    private function getStudentRank(Student $student, int $academicYearId, ?StudyGroup $rombel): int
    {
        if (! $rombel) {
            return 1;
        }

        // Get all students in this rombel
        $studentsInRombel = Student::whereHas('studyGroups', function ($q) use ($rombel) {
            $q->where('study_groups.id', $rombel->id);
        })->get();

        $studentIds = $studentsInRombel->pluck('id')->toArray();

        // Fetch all grades for all students in the rombel in one query
        $allGrades = Grade::whereIn('student_id', $studentIds)
            ->where('academic_year_id', $academicYearId)
            ->get()
            ->groupBy('student_id');

        $studentAverages = [];
        foreach ($studentsInRombel as $s) {
            $grades = $allGrades->get($s->id) ?? collect();

            if ($grades->isEmpty()) {
                $studentAverages[$s->id] = 0;

                continue;
            }

            $total = 0;
            foreach ($grades as $g) {
                $total += round(($g->nilai_tugas + $g->nilai_uts + $g->nilai_uas) / 3);
            }
            $studentAverages[$s->id] = $total / $grades->count();
        }

        // Sort by average descending
        arsort($studentAverages);

        $rank = 1;
        foreach ($studentAverages as $studentId => $average) {
            if ($studentId == $student->id) {
                return $rank;
            }
            $rank++;
        }

        return 1;
    }

    /**
     * Generate and persist the student report using AI analysis.
     */
    public function generateStudentRapor(Student $student, int $academicYearId): StudentRapor
    {
        $student->loadMissing(['user', 'studyGroups.level']);

        $rombel = $student->studyGroups->where('academic_year_id', $academicYearId)->first();
        $level = $rombel?->level;

        $subjectsData = $this->getSubjectsData($student, $academicYearId, $level);
        $attendance = $this->getAttendanceRecap($student, $academicYearId);
        $rank = $this->getStudentRank($student, $academicYearId, $rombel);

        $totalStudents = 1;
        if ($rombel) {
            $totalStudents = Student::whereHas('studyGroups', fn ($q) => $q->where('study_groups.id', $rombel->id))->count();
        }

        $gradesCount = 0;
        $gradesSum = 0;
        foreach ($subjectsData as $sub) {
            if ($sub['nilai'] !== null) {
                $gradesCount++;
                $gradesSum += $sub['nilai'];
            }
        }
        $averageGrade = $gradesCount > 0 ? round($gradesSum / $gradesCount, 1) : 0;

        $subjectsSummary = '';
        foreach ($subjectsData as $sub) {
            $nilaiStr = $sub['nilai'] !== null ? $sub['nilai'] : 'Belum dinilai';
            $subjectsSummary .= "- {$sub['nama']}: Nilai {$nilaiStr}\n";
        }

        $aiCatatan = null;
        try {
            $settings = ChatbotSetting::current();
            if ($settings->is_active) {
                $systemInstruction = 'Kamu adalah Wali Kelas yang bijak, perhatian, dan profesional di sekolah dasar (SD). Tugasmu adalah merumuskan Catatan Wali Kelas yang singkat, ramah, dan memotivasi untuk lembar rapor berdasarkan data akademik siswa. Catatan harus ringkas (maksimal 40-50 kata, 2-3 kalimat), menyoroti prestasinya jika bagus, atau memberikan saran perbaikan yang hangat jika nilainya kurang. Jangan gunakan teks pengantar atau penutup tambahan, kembalikan HANYA narasi catatan tersebut.';

                $userPrompt = "Nama Murid: {$student->user->name}\n".
                    "Rata-rata Nilai: {$averageGrade}\n".
                    "Peringkat: {$rank} dari {$totalStudents} siswa\n".
                    "Kehadiran: Sakit {$attendance['sakit']} hari, Izin {$attendance['izin']} hari, Alpa {$attendance['alpha']} hari\n".
                    "Ringkasan Nilai:\n{$subjectsSummary}";

                $agent = new WaliKelasAgent($systemInstruction);

                $provider = $settings->provider ?: 'gemini';
                $model = $settings->getModelFor($provider);

                $response = $agent->prompt($userPrompt, provider: $provider, model: $model);

                $aiCatatan = trim(strip_tags((string) $response));
            }
        } catch (\Exception $e) {
            Log::warning('AI Rapor Generation failed: '.$e->getMessage());
        }

        if (empty($aiCatatan)) {
            $aiCatatan = "Selamat {$student->user->name}, kamu berhasil meraih peringkat ke-{$rank} dengan rata-rata nilai {$averageGrade}. Pertahankan semangat belajarmu dan teruslah berprestasi!";
        }

        $activeYear = AcademicYear::find($academicYearId);
        $isGenap = $activeYear && strtolower($activeYear->semester) === 'genap';
        $isNaik = null;
        $kenaikanKelasTo = null;

        if ($isGenap) {
            $isNaik = $averageGrade >= 70;

            if ($level) {
                $levelName = $level->nama_tingkatan;
                if (preg_match('/\d+/', $levelName, $matches)) {
                    $currentLevelNum = (int) $matches[0];
                    $nextLevelNum = $currentLevelNum + 1;
                    if ($nextLevelNum <= 6) {
                        $levelWords = [
                            2 => 'II (Dua)',
                            3 => 'III (Tiga)',
                            4 => 'IV (Empat)',
                            5 => 'V (Lima)',
                            6 => 'VI (Enam)',
                        ];
                        $kenaikanKelasTo = $levelWords[$nextLevelNum] ?? '.......';
                    } else {
                        $kenaikanKelasTo = 'SMP / Sederajat';
                    }
                }
            }
        }

        return StudentRapor::updateOrCreate(
            [
                'student_id' => $student->id,
                'academic_year_id' => $academicYearId,
            ],
            [
                'sakit' => $attendance['sakit'],
                'izin' => $attendance['izin'],
                'alpha' => $attendance['alpha'],
                'catatan_wali_kelas' => $aiCatatan,
                'is_naik' => $isNaik,
                'kenaikan_kelas_to' => $kenaikanKelasTo,
            ]
        );
    }
}
