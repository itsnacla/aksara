<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Extracurricular;
use App\Models\ExtracurricularGrade;
use App\Models\Grade;
use App\Models\LearningObjective;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\StudentGrade;
use App\Models\StudentRapor;
use App\Models\StudyGroup;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimeSlot;
use Illuminate\Database\Seeder;

class GradesAndReportsSeeder extends Seeder
{
    protected $academicYear;

    public function run(): void
    {
        // Set random seed untuk konsistensi
        srand(42);
        mt_srand(42);

        // Ambil academic year yang aktif
        $this->academicYear = AcademicYear::where('is_active', true)->first();
        if (!$this->academicYear) {
            $this->academicYear = AcademicYear::first();
        }

        $this->command->info('📚 Seeding Grades and Reports for Academic Year: ' . $this->academicYear->tahun_ajaran);

        // Seed prerequisite data
        $this->seedLearningObjectives();
        $this->seedSchedules();
        
        // Seed grading data
        $this->seedSubjectGrades();
        $this->seedLearningObjectiveGrades();
        $this->seedExtracurricularGrades();
        $this->seedStudentRapors();

        $this->command->info('✅ Grades and Reports seeding completed successfully!');
    }

    /**
     * Seed Learning Objectives (Capaian Pembelajaran) untuk setiap mata pelajaran
     */
    protected function seedLearningObjectives(): void
    {
        $this->command->info('🎯 Seeding Learning Objectives...');

        $subjects = Subject::where('is_graded', true)->get();
        $levels = \App\Models\Level::all();

        $exampleObjectives = [
            'Memahami konsep dasar',
            'Analisis kritis',
            'Komunikasi efektif',
            'Kolaborasi tim',
            'Pengambilan keputusan',
            'Kreativitas inovatif',
            'Nilai universal',
            'Keterampilan praktis',
        ];

        foreach ($subjects as $subject) {
            foreach ($levels as $level) {
                for ($i = 1; $i <= 3; $i++) {
                    $code = substr($subject->kode_mapel, 0, 8) . '-' . substr($level->nama_tingkatan, 0, 3) . $i;
                    
                    LearningObjective::firstOrCreate(
                        [
                            'subject_id' => $subject->id,
                            'level_id' => $level->id,
                            'code' => $code,
                            'academic_year_id' => $this->academicYear->id,
                        ],
                        [
                            'description' => $exampleObjectives[array_rand($exampleObjectives)],
                            'is_active' => true,
                        ]
                    );
                }
            }
        }

        $objectiveCount = LearningObjective::where('academic_year_id', $this->academicYear->id)->count();
        $this->command->line("✓ Created {$objectiveCount} learning objectives");
    }

    /**
     * Seed Schedule (Jadwal Mengajar) untuk setiap mata pelajaran di rombel
     */
    protected function seedSchedules(): void
    {
        $this->command->info('📅 Seeding Schedules (Jadwal Mengajar)...');

        $studyGroups = StudyGroup::where('academic_year_id', $this->academicYear->id)->get();
        $subjects = Subject::all();
        $teachers = Teacher::where('is_walikelas', false)->where('status', 'aktif')->get();
        $timeSlots = TimeSlot::where('is_istirahat', false)->orderBy('urutan')->get();

        if ($timeSlots->isEmpty() || $teachers->isEmpty()) {
            $this->command->warn('⚠ No time slots or teachers found, skipping schedules');
            return;
        }

        $dayOfWeek = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $dayIndex = 0;
        $slotIndex = 0;

        foreach ($studyGroups as $studyGroup) {
            $walikelas = Teacher::find($studyGroup->walikelas_id);
            $isActiveWalikelas = $walikelas && $walikelas->status === 'aktif';

            foreach ($subjects as $subject) {
                $existingSchedule = Schedule::where([
                    'study_group_id' => $studyGroup->id,
                    'subject_id' => $subject->id,
                ])->first();

                if (!$existingSchedule) {
                    $teacherId = null;

                    if ($subject->is_umum && $isActiveWalikelas) {
                        $teacherId = $studyGroup->walikelas_id;
                    } else if ($teachers->isNotEmpty()) {
                        $teacherId = $teachers->random()->id;
                    }

                    if ($teacherId) {
                        $timeSlot = $timeSlots[$slotIndex % $timeSlots->count()];

                        Schedule::create([
                            'study_group_id' => $studyGroup->id,
                            'subject_id' => $subject->id,
                            'teacher_id' => $teacherId,
                            'hari' => $dayOfWeek[$dayIndex % 5],
                            'start_time_slot_id' => $timeSlot->id,
                            'end_time_slot_id' => $timeSlot->id,
                        ]);

                        $slotIndex++;
                        if ($slotIndex % $timeSlots->count() === 0) {
                            $dayIndex++;
                        }
                    }
                }
            }
        }

        $scheduleCount = Schedule::count();
        $this->command->line("✓ Created {$scheduleCount} schedules");
    }

    /**
     * Seed nilai mapel (Tugas, UTS, UAS)
     */
    protected function seedSubjectGrades(): void
    {
        $this->command->info('📊 Seeding Subject Grades (Nilai Mapel)...');

        $students = Student::with('studyGroups')->get();
        $schedules = Schedule::with('subject', 'teacher', 'studyGroup')->get();

        if ($schedules->isEmpty()) {
            $this->command->warn('⚠ No schedules found, skipping subject grades');
            return;
        }

        foreach ($students as $student) {
            // Dapatkan study group siswa yang sesuai tahun akademik
            $studentStudyGroups = $student->studyGroups
                ->where('academic_year_id', $this->academicYear->id)
                ->pluck('id')
                ->toArray();

            if (empty($studentStudyGroups)) {
                continue;
            }

            // Ambil jadwal yang sesuai dengan rombel siswa
            $studentSchedules = $schedules->filter(function ($schedule) use ($studentStudyGroups) {
                return in_array($schedule->study_group_id, $studentStudyGroups) && $schedule->subject && $schedule->subject->is_graded;
            })->unique('subject_id');

            foreach ($studentSchedules as $schedule) {
                // Cek apakah sudah ada nilai untuk siswa ini
                $existingGrade = Grade::where([
                    'student_id' => $student->id,
                    'subject_id' => $schedule->subject_id,
                    'academic_year_id' => $this->academicYear->id,
                    'study_group_id' => $schedule->study_group_id,
                ])->first();

                if (!$existingGrade) {
                    // Generate nilai dengan distribusi lebih realistis
                    $baseScore = rand(70, 100);
                    Grade::create([
                        'student_id' => $student->id,
                        'subject_id' => $schedule->subject_id,
                        'teacher_id' => $schedule->teacher_id,
                        'academic_year_id' => $this->academicYear->id,
                        'study_group_id' => $schedule->study_group_id,
                        'nilai_tugas' => max(0, min(100, $baseScore + rand(-10, 5))),
                        'nilai_uts' => max(0, min(100, $baseScore + rand(-15, 0))),
                        'nilai_uas' => max(0, min(100, $baseScore + rand(-10, 5))),
                    ]);
                }
            }
        }

        $gradeCount = Grade::where('academic_year_id', $this->academicYear->id)->count();
        $this->command->line("✓ Created {$gradeCount} subject grades");
    }

    /**
     * Seed nilai learning objective (CP)
     */
    protected function seedLearningObjectiveGrades(): void
    {
        $this->command->info('📋 Seeding Learning Objective Grades (Capaian Pembelajaran)...');

        $students = Student::all();
        $learningObjectives = LearningObjective::with('subject')
            ->whereHas('subject', function($q) {
                $q->where('is_graded', true);
            })
            ->where('academic_year_id', $this->academicYear->id)
            ->get();

        if ($learningObjectives->isEmpty()) {
            $this->command->warn('⚠ No learning objectives found, skipping student grades');
            return;
        }

        foreach ($students as $student) {
            foreach ($learningObjectives as $objective) {
                // Cek apakah sudah ada nilai
                $existingGrade = StudentGrade::where([
                    'student_id' => $student->id,
                    'learning_objective_id' => $objective->id,
                    'academic_year_id' => $this->academicYear->id,
                ])->first();

                if (!$existingGrade) {
                    // Ambil teacher dari subjek atau ambil yang pertama
                    $teacher = null;
                    
                    if ($objective->subject) {
                        $teacher = $objective->subject->teachers()->first();
                    }
                    
                    if (!$teacher) {
                        $teacher = Teacher::first();
                    }

                    if ($teacher) {
                        StudentGrade::create([
                            'student_id' => $student->id,
                            'learning_objective_id' => $objective->id,
                            'academic_year_id' => $this->academicYear->id,
                            'teacher_id' => $teacher->id,
                            'score' => number_format(rand(70, 100) + rand(0, 9) / 10, 1), // 70.0 - 100.0
                            'is_achieved' => rand(1, 100) > 20, // 80% mencapai kompetensi
                            'notes' => $this->generateRandomNotes(),
                        ]);
                    }
                }
            }
        }

        $studentGradeCount = StudentGrade::where('academic_year_id', $this->academicYear->id)->count();
        $this->command->line("✓ Created {$studentGradeCount} learning objective grades");
    }

    /**
     * Seed nilai ekstrakurikuler
     */
    protected function seedExtracurricularGrades(): void
    {
        $this->command->info('🏅 Seeding Extracurricular Grades...');

        $students = Student::with('user')->get();
        $extracurriculars = Extracurricular::get();

        if ($extracurriculars->isEmpty()) {
            $this->command->warn('⚠ No extracurriculars found, skipping extracurricular grades');
            return;
        }

        foreach ($students as $student) {
            // Setiap siswa mengambil 2-3 ekstrakurikuler
            $numExtracurriculars = rand(2, 3);
            $randomExtracurriculars = $extracurriculars->random(min($numExtracurriculars, $extracurriculars->count()));

            foreach ($randomExtracurriculars as $extracurricular) {
                $existingGrade = ExtracurricularGrade::where([
                    'student_id' => $student->id,
                    'extracurricular_id' => $extracurricular->id,
                    'academic_year_id' => $this->academicYear->id,
                ])->first();

                if (!$existingGrade) {
                    $predikat = $this->generateRandomPredikat();
                    ExtracurricularGrade::create([
                        'student_id' => $student->id,
                        'extracurricular_id' => $extracurricular->id,
                        'academic_year_id' => $this->academicYear->id,
                        'predikat' => $predikat,
                        'keterangan' => ExtracurricularGrade::$defaultKeterangan[$predikat] ?? '',
                    ]);
                }
            }
        }

        $extracurricularGradeCount = ExtracurricularGrade::where('academic_year_id', $this->academicYear->id)->count();
        $this->command->line("✓ Created {$extracurricularGradeCount} extracurricular grades");
    }

    /**
     * Seed student rapor (kehadiran, kenaikan kelas)
     */
    protected function seedStudentRapors(): void
    {
        $this->command->info('📄 Seeding Student Rapors (Attendance & Class Promotion)...');

        $students = Student::with('user')->get();
        $levels = \App\Models\Level::all();

        foreach ($students as $student) {
            $existingRapor = StudentRapor::where([
                'student_id' => $student->id,
                'academic_year_id' => $this->academicYear->id,
            ])->first();

            if (!$existingRapor) {
                // Data kehadiran
                $totalDays = 180; // Hari sekolah dalam setahun
                $hadir = rand(160, 180);
                $sakit = rand(1, 10);
                $izin = rand(0, 5);
                $alpha = max(0, $totalDays - $hadir - $sakit - $izin);

                // Tentukan kenaikan kelas (90% naik)
                $isNaik = rand(1, 100) > 10;
                $studyGroup = $student->studyGroups()
                    ->where('academic_year_id', $this->academicYear->id)
                    ->first();
                
                $kelasSekarang = $studyGroup?->level;
                $kenaiKanKeTo = null;

                if ($isNaik && $kelasSekarang && $levels->count() > 0) {
                    // Cari level berikutnya berdasarkan level sekarang
                    // Kelas 1->2, Kelas 2->3, dst
                    $levelOrder = $levels->pluck('nama_tingkatan')->values();
                    $currentIndex = $levelOrder->search($kelasSekarang->nama_tingkatan);
                    
                    if ($currentIndex !== false && $currentIndex < $levelOrder->count() - 1) {
                        $nextLevelName = $levelOrder[$currentIndex + 1];
                        $nextLevel = $levels->firstWhere('nama_tingkatan', $nextLevelName);
                        $kenaiKanKeTo = $nextLevel?->id;
                    }
                }

                StudentRapor::create([
                    'student_id' => $student->id,
                    'academic_year_id' => $this->academicYear->id,
                    'sakit' => $sakit,
                    'izin' => $izin,
                    'alpha' => $alpha,
                    'catatan_wali_kelas' => $this->generateRandomRaporNotes(),
                    'is_naik' => $isNaik,
                    'kenaikan_kelas_to' => $kenaiKanKeTo,
                    'is_published' => false,
                ]);
            }
        }

        $raporCount = StudentRapor::where('academic_year_id', $this->academicYear->id)->count();
        $this->command->line("✓ Created {$raporCount} student rapors");
    }

    /**
     * Generate random learning objective notes
     */
    protected function generateRandomNotes(): string
    {
        $notes = [
            'Siswa menunjukkan pemahaman yang sangat baik.',
            'Perlu latihan lebih lanjut untuk penguatan konsep.',
            'Siswa sangat aktif dan responsif dalam pembelajaran.',
            'Masih perlu bimbingan dalam beberapa aspek kompetensi.',
            'Pencapaian siswa sudah melampaui standar yang ditetapkan.',
            'Siswa menunjukkan perkembangan yang positif.',
            'Perlu perhatian khusus untuk pengembangan kompetensi ini.',
            'Siswa mampu bekerja sama dengan baik dengan teman-teman.',
            'Sudah mencapai kompetensi minimal dengan baik.',
            'Siswa perlu meningkatkan fokus dan ketelitian.',
        ];

        return $notes[array_rand($notes)];
    }

    /**
     * Generate random rapor notes from wali kelas
     */
    protected function generateRandomRaporNotes(): string
    {
        $notes = [
            'Siswa memiliki prestasi akademik yang memuaskan. Terus tingkatkan semangat belajar.',
            'Perlu meningkatkan kedisiplinan dan kehadiran. Potensi akademik masih bisa ditingkatkan.',
            'Prestasi sangat baik. Siswa adalah teladan bagi teman-temannya.',
            'Siswa cukup baik namun perlu lebih fokus dalam pelajaran matematika.',
            'Sangat baik dalam hal kepribadian dan akhlak. Akademik sudah memenuhi target.',
            'Siswa menunjukkan peningkatan prestasi yang signifikan.',
            'Terus pertahankan prestasi yang sudah dicapai. Jangan berhenti belajar.',
            'Siswa memiliki potensi yang baik, manfaatkan dengan sebaik-baiknya.',
            'Perlu lebih banyak interaksi dengan guru untuk konsultasi akademik.',
            'Siswa aktif dalam kegiatan sekolah. Prestasi akademik juga memuaskan.',
        ];

        return $notes[array_rand($notes)];
    }

    /**
     * Generate random extracurricular predikat
     */
    protected function generateRandomPredikat(): string
    {
        $random = rand(1, 100);
        if ($random <= 20) {
            return 'A'; // 20% Sangat Baik
        } elseif ($random <= 60) {
            return 'B'; // 40% Baik
        } elseif ($random <= 90) {
            return 'C'; // 30% Cukup
        } else {
            return 'D'; // 10% Kurang
        }
    }
}
