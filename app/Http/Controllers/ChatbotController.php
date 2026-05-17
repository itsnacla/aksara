<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Grade;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Classroom;
use App\Models\EReport;
use App\Models\Extracurricular;
use Illuminate\Support\Facades\Storage;

class ChatbotController extends Controller
{
    /**
     * Handle an incoming chat message and return an AI response.
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'conversation_id' => 'nullable|string', // Used by Laravel AI SDK
        ]);

        $userMessage = $request->input('message');
        $conversationId = $request->input('conversation_id');
        $user = $request->user();

        try {
            $agent = new \App\Ai\Agents\AksaraAssistant($user);
            
            if ($conversationId) {
                $response = $agent->continue($conversationId, as: $user)->prompt($userMessage);
            } else {
                $response = $agent->forUser($user)->prompt($userMessage);
            }

            $reply = (string) $response;
            $conversationId = $response->conversationId;

            // Dispatch event for real-time update via Reverb
            event(new \App\Events\MessageSent($conversationId, $reply));

            return response()->json([
                'reply' => $reply,
                'conversation_id' => $conversationId,
            ]);
        } catch (\Exception $e) {
            Log::error('Chatbot AI SDK error: ' . $e->getMessage());
            $reply = $this->getFallbackResponse($userMessage, $this->getUserRole($user));
            
            return response()->json([
                'reply' => $reply,
            ]);
        }
    }

    /**
     * Return the user's role configuration for the chatbot widget.
     * Called via GET to customize the chatbot UI per role.
     */
    public function config(Request $request)
    {
        $user = $request->user();
        $role = $this->getUserRole($user);

        return response()->json([
            'role' => $role,
            'roleName' => $this->getRoleDisplayName($role),
            'greeting' => $this->getRoleGreeting($role, $user),
            'chips' => $this->getRoleChips($role),
        ]);
    }

    /**
     * Determine the user's primary role.
     */
    private function getUserRole($user): string
    {
        if (!$user) {
            return 'guest';
        }

        $roleName = $user->roles->first()?->name ?? '';

        return match (true) {
            str_contains(strtolower($roleName), 'super_admin'),
            str_contains(strtolower($roleName), 'admin') => 'admin',
            str_contains(strtolower($roleName), 'guru'),
            str_contains(strtolower($roleName), 'teacher') => 'guru',
            str_contains(strtolower($roleName), 'staff') => 'staff',
            str_contains(strtolower($roleName), 'orang_tua'),
            str_contains(strtolower($roleName), 'parent'),
            str_contains(strtolower($roleName), 'wali') => 'orang_tua',
            str_contains(strtolower($roleName), 'siswa'),
            str_contains(strtolower($roleName), 'student') => 'siswa',
            default => 'siswa',
        };
    }

    private function getRoleDisplayName(string $role): string
    {
        return match ($role) {
            'admin' => 'Administrator',
            'guru' => 'Guru',
            'staff' => 'Staff',
            'orang_tua' => 'Orang Tua',
            'siswa' => 'Siswa',
            default => 'Pengguna',
        };
    }

    private function getRoleGreeting(string $role, $user): string
    {
        $name = $user->name ?? 'Pengguna';

        return match ($role) {
            'admin' => "Halo, {$name}! 👋 Saya **Aksara AI**, asisten administrasi sekolah Anda. Saya bisa membantu dengan manajemen data siswa, guru, kelas, laporan, dan pengaturan sistem. Ada yang bisa saya bantu?",
            'guru' => "Selamat datang, Pak/Bu {$name}! 👋 Saya **Aksara AI**. Saya bisa membantu dengan jadwal mengajar, input nilai, data presensi siswa, dan manajemen kelas Anda. Silakan bertanya!",
            'staff' => "Halo, {$name}! 👋 Saya **Aksara AI**, asisten administrasi. Saya siap membantu dengan pengelolaan data sekolah, surat-menyurat, dan tugas administrasi lainnya.",
            'orang_tua' => "Halo, Bapak/Ibu {$name}! 👋 Saya **Aksara AI**. Saya bisa membantu Anda memantau perkembangan akademik anak, presensi, nilai, dan informasi sekolah lainnya.",
            'siswa' => "Halo, {$name}! 👋 Saya **Aksara AI**, asisten virtual sekolah. Saya bisa membantu kamu dengan jadwal, nilai, presensi, dan informasi sekolah lainnya. Mau tanya apa?",
            default => "Halo! 👋 Saya **Aksara AI**, asisten virtual Aksara System. Ada yang bisa saya bantu?",
        };
    }

    private function getRoleChips(string $role): array
    {
        return match ($role) {
            'admin' => [
                ['label' => '👥 Data Siswa', 'message' => 'Bagaimana cara mengelola data siswa?'],
                ['label' => '👨‍🏫 Data Guru', 'message' => 'Bagaimana cara mengelola data guru?'],
                ['label' => '📊 Laporan', 'message' => 'Bagaimana cara melihat laporan sekolah?'],
                ['label' => '⚙️ Pengaturan', 'message' => 'Bagaimana cara mengatur sistem?'],
            ],
            'guru' => [
                ['label' => '📅 Jadwal Mengajar', 'message' => 'Bagaimana cara melihat jadwal mengajar saya?'],
                ['label' => '📝 Input Nilai', 'message' => 'Bagaimana cara menginput nilai siswa?'],
                ['label' => '📋 Presensi Kelas', 'message' => 'Bagaimana cara melihat presensi kelas saya?'],
                ['label' => '🏫 Wali Kelas', 'message' => 'Bagaimana cara mengelola kelas perwalian saya?'],
            ],
            'staff' => [
                ['label' => '📄 Surat', 'message' => 'Bagaimana cara mengelola surat-menyurat?'],
                ['label' => '👥 Data Master', 'message' => 'Bagaimana cara mengelola data master?'],
                ['label' => '📊 Rekap', 'message' => 'Bagaimana cara membuat rekap data?'],
                ['label' => '🔔 Pengumuman', 'message' => 'Bagaimana cara membuat pengumuman?'],
            ],
            'orang_tua' => [
                ['label' => '📊 Nilai Anak', 'message' => 'Bagaimana cara melihat nilai anak saya?'],
                ['label' => '📋 Presensi Anak', 'message' => 'Bagaimana cara melihat presensi anak saya?'],
                ['label' => '📄 Rapor', 'message' => 'Bagaimana cara mengunduh rapor anak saya?'],
                ['label' => '📅 Jadwal', 'message' => 'Bagaimana jadwal pelajaran anak saya?'],
            ],
            'siswa' => [
                ['label' => '📅 Jadwal', 'message' => 'Bagaimana cara melihat jadwal saya?'],
                ['label' => '📊 Nilai', 'message' => 'Bagaimana cara melihat nilai saya?'],
                ['label' => '📋 Presensi', 'message' => 'Bagaimana cara melihat presensi saya?'],
                ['label' => '📄 Rapor', 'message' => 'Bagaimana cara mengunduh rapor saya?'],
            ],
            default => [
                ['label' => '❓ Bantuan', 'message' => 'Apa saja yang bisa kamu bantu?'],
            ],
        };
    }

    private function buildSystemInstruction(string $role, $user): string
    {
        $name = $user->name ?? 'Pengguna';
        $roleName = $this->getRoleDisplayName($role);

        $base = "Kamu adalah Aksara AI, asisten virtual cerdas untuk sistem manajemen sekolah bernama Aksara System. " .
            "Jawab dengan ramah, ringkas, dan dalam Bahasa Indonesia. " .
            "Kamu juga bisa menjawab pertanyaan umum di luar konteks sekolah seperti pengetahuan umum, sains, matematika, sejarah, coding, dan topik lainnya. " .
            "Tetap ramah dan conversational layaknya AI chatbot modern. " .
            "Nama pengguna saat ini: {$name}. Role: {$roleName}. ";

        $roleContext = match ($role) {
            'admin' => "Pengguna ini adalah Administrator/Super Admin dengan akses penuh ke sistem. " .
            "Kamu bisa membantu dengan: manajemen data siswa, guru, staff, dan orang tua; " .
            "pengaturan tahun ajaran dan kelas; manajemen hak akses (roles & permissions via Filament Shield); " .
            "melihat laporan dan statistik sekolah; pengaturan sistem secara keseluruhan. " .
            "Admin mengakses sistem melalui panel Filament di /admin. " .
            "Berikan jawaban yang detail dan teknis jika diperlukan.",

            'guru' => "Pengguna ini adalah Guru. " .
            "Kamu bisa membantu dengan: melihat jadwal mengajar; menginput dan mengelola nilai siswa; " .
            "melihat data presensi siswa di kelasnya; mengelola kelas perwalian (jika wali kelas); " .
            "membuat catatan akademik. " .
            "Guru mengakses sistem melalui panel Filament di /admin. " .
            "Berikan jawaban yang profesional dan suportif.",

            'staff' => "Pengguna ini adalah Staff Tata Usaha/Administrasi. " .
            "Kamu bisa membantu dengan: pengelolaan data master (siswa, guru, kelas); " .
            "administrasi surat-menyurat; rekap data presensi dan nilai; " .
            "pengelolaan pengumuman dan informasi sekolah. " .
            "Staff mengakses sistem melalui panel Filament di /admin. " .
            "Berikan jawaban yang praktis dan jelas.",

            'wali' => "Pengguna ini adalah Orang Tua/Wali Murid. " .
            "Kamu bisa membantu dengan: memantau nilai akademik anak; " .
            "melihat data presensi/kehadiran anak; mengunduh rapor digital anak; " .
            "melihat jadwal pelajaran anak; menghubungi wali kelas. " .
            "Orang tua mengakses portal di /dashboard. " .
            "Berikan jawaban yang mudah dipahami dan menenangkan.",

            'siswa' => "Pengguna ini adalah Siswa. " .
            "Kamu bisa membantu dengan: melihat jadwal pelajaran; melihat nilai dan rapor; " .
            "melihat data presensi/kehadiran; informasi tentang kegiatan ekstrakurikuler; " .
            "informasi umum tentang sekolah. " .
            "Siswa mengakses portal di /dashboard. " .
            "Berikan jawaban yang ramah dan mudah dipahami. Gunakan bahasa yang santai tapi tetap sopan.",

            default => "Berikan jawaban yang ramah dan informatif.",
        };

        return $base . $roleContext . " Jika kamu memerlukan data spesifik untuk menjawab pertanyaan (seperti nilai, jadwal, atau daftar siswa), gunakan tool yang tersedia. JANGAN menebak data jika tidak ada.";
    }

    /**
     * Get normalized tool definitions based on user role.
     */
    private function getToolDefinitions(string $role): array
    {
        $tools = [
            [
                'name' => 'get_academic_data',
                'description' => 'Mendapatkan data nilai (tugas, UTS, UAS) dan absensi siswa untuk analisis atau informasi.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'student_id' => [
                            'type' => 'integer',
                            'description' => 'ID siswa (opsional jika siswa menanyakan miliknya sendiri).',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'get_schedule_data',
                'description' => 'Mendapatkan jadwal pelajaran berdasarkan kelas atau guru.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'study_group_id' => [
                            'type' => 'integer',
                            'description' => 'ID Rombel/Kelas (opsional).',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'get_report_link',
                'description' => 'Mendapatkan link unduh rapor digital (PDF) siswa.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'student_id' => [
                            'type' => 'integer',
                            'description' => 'ID siswa (opsional).',
                        ],
                    ],
                ],
            ],
        ];

        $tools[] = [
            'name' => 'get_extracurricular_data',
            'description' => 'Mendapatkan data kegiatan ekstrakurikuler siswa beserta nilai kualitatif.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'student_id' => [
                        'type' => 'integer',
                        'description' => 'ID siswa (opsional jika siswa menanyakan miliknya sendiri).',
                    ],
                ],
            ],
        ];

        if (in_array($role, ['admin', 'guru', 'staff'])) {
            $tools[] = [
                'name' => 'get_classroom_info',
                'description' => 'Mendapatkan daftar siswa dalam suatu kelas (untuk guru/admin).',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'study_group_id' => [
                            'type' => 'integer',
                            'description' => 'ID Rombel/Kelas.',
                        ],
                    ],
                ],
            ];
        }

        return $tools;
    }

    /**
     * Handle function calls from AI — dispatches to secure fetchers.
     */
    private function handleFunctionCall(string $name, array $args, $user)
    {
        $role = $this->getUserRole($user);

        return match ($name) {
            'get_academic_data' => $this->fetchAcademicData($args['student_id'] ?? null, $user, $role),
            'get_schedule_data' => $this->fetchScheduleData($args['study_group_id'] ?? null, $user, $role),
            'get_classroom_info' => $this->fetchStudyGroupInfo($args['study_group_id'] ?? null, $user, $role),
            'get_report_link' => $this->fetchReportLink($args['student_id'] ?? null, $user, $role),
            'get_extracurricular_data' => $this->fetchExtracurricularData($args['student_id'] ?? null, $user, $role),
            default => ['error' => 'Fungsi tidak ditemukan.'],
        };
    }

    // ========================================================================
    // SECURE DATA FETCHERS — filtered by authenticated user
    // ========================================================================

    private function fetchAcademicData(?int $studentId, $user, string $role)
    {
        $query = Grade::with(['subject', 'academicYear']);

        if ($role === 'siswa') {
            $student = $user->student;
            if (!$student) return ['error' => 'Data siswa tidak ditemukan.'];
            $query->where('student_id', $student->id);
        } elseif ($role === 'orang_tua') {
            $parent = $user->parent;
            if (!$parent) return ['error' => 'Data orang tua tidak ditemukan.'];
            $childIds = $parent->students->pluck('id')->toArray();
            if ($studentId && in_array($studentId, $childIds)) {
                $query->where('student_id', $studentId);
            } else {
                $query->whereIn('student_id', $childIds);
            }
        } elseif ($role === 'guru') {
            if ($studentId) {
                $query->where('student_id', $studentId);
            } else {
                return ['error' => 'Mohon tentukan ID siswa.'];
            }
        } elseif ($role === 'admin') {
            if ($studentId) $query->where('student_id', $studentId);
        }

        $grades = $query->latest()->limit(20)->get()->map(fn($g) => [
            'subject' => $g->subject->nama_pelajaran ?? 'N/A',
            'tugas' => $g->nilai_tugas,
            'uts' => $g->nilai_uts,
            'uas' => $g->nilai_uas,
            'tahun' => $g->academicYear->tahun ?? 'N/A',
        ]);

        $attendanceQuery = Attendance::query();
        if ($role === 'siswa') $attendanceQuery->where('student_id', $user->student->id);
        elseif ($role === 'orang_tua') $attendanceQuery->whereIn('student_id', $user->parent->students->pluck('id')->toArray());
        elseif ($studentId) $attendanceQuery->where('student_id', $studentId);

        $attendance = $attendanceQuery->selectRaw('status, count(*) as count')->groupBy('status')->get();

        return [
            'grades' => $grades,
            'attendance_summary' => $attendance,
            'context' => 'Data ini rahasia dan hanya boleh ditampilkan kepada user yang berhak.',
        ];
    }

    private function fetchScheduleData(?int $studyGroupId, $user, string $role)
    {
        $query = Schedule::with(['subject', 'studyGroup.classroom', 'teacher.user']);

        if ($role === 'siswa') {
            $activeRombelId = $user->student->studyGroups()
                ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
                ->first()?->id ?? 0;
            $query->where('study_group_id', $activeRombelId);
        } elseif ($role === 'guru') {
            $query->where('teacher_id', $user->teacher->id ?? 0);
        } elseif ($studyGroupId) {
            $query->where('study_group_id', $studyGroupId);
        }

        return $query->get()->map(fn($s) => [
            'hari' => $s->hari,
            'jam' => "{$s->jam_mulai} - {$s->jam_selesai}",
            'mapel' => $s->subject->nama_pelajaran ?? 'N/A',
            'rombel' => $s->studyGroup->nama_rombel ?? 'N/A',
            'ruangan' => $s->studyGroup->classroom->nama_ruangan ?? 'N/A',
            'guru' => $s->teacher->user->name ?? 'N/A',
        ]);
    }

    private function fetchStudyGroupInfo(?int $studyGroupId, $user, string $role)
    {
        if (!in_array($role, ['admin', 'guru', 'staff'])) return ['error' => 'Unauthorized'];

        $query = \App\Models\StudyGroup::with('students.user');

        if ($role === 'guru') {
            $query->where('walikelas_id', $user->teacher->id ?? 0);
        } elseif ($studyGroupId) {
            $query->where('id', $studyGroupId);
        }

        $studyGroup = $query->first();
        if (!$studyGroup) return ['error' => 'Rombel tidak ditemukan.'];

        return [
            'nama_rombel' => $studyGroup->nama_rombel,
            'total_siswa' => $studyGroup->students->count(),
            'daftar_siswa' => $studyGroup->students->map(fn($s) => [
                'id' => $s->id,
                'nama' => $s->user->name,
                'nisn' => $s->nisn,
            ]),
        ];
    }

    private function fetchReportLink(?int $studentId, $user, string $role)
    {
        $query = EReport::with('academicYear');

        if ($role === 'siswa') {
            $query->where('student_id', $user->student->id ?? 0);
        } elseif ($role === 'orang_tua') {
            $childIds = $user->parent->students->pluck('id')->toArray();
            if ($studentId && in_array($studentId, $childIds)) {
                $query->where('student_id', $studentId);
            } else {
                $query->whereIn('student_id', $childIds);
            }
        } elseif ($studentId) {
            $query->where('student_id', $studentId);
        }

        $report = $query->latest()->first();
        if (!$report) return ['error' => 'Rapor tidak ditemukan.'];

        return [
            'semester' => $report->semester,
            'tahun_ajaran' => $report->academicYear->tahun ?? 'N/A',
            'download_url' => url(Storage::url($report->file_path)),
            'keterangan' => 'Berikan link ini kepada user untuk diunduh.',
        ];
    }

    private function fetchExtracurricularData(?int $studentId, $user, string $role)
    {
        // Now fetching master data, no longer per student
        $extracurriculars = Extracurricular::orderBy('kategori', 'asc')
            ->orderBy('nama_ekskul', 'asc')
            ->get()
            ->map(fn($e) => [
                'nama_ekskul' => $e->nama_ekskul,
                'kategori' => $e->kategori,
                'min_nilai' => $e->nilai_minimum ?? 'N/A',
                'pembina' => $e->pembina ?? 'N/A',
                'deskripsi' => $e->deskripsi,
            ]);

        return [
            'extracurriculars' => $extracurriculars,
            'total' => $extracurriculars->count(),
            'context' => 'Daftar kegiatan ekstrakurikuler yang tersedia di sekolah (Master Data).',
        ];
    }

    // ========================================================================
    // AI RESPONSE — uses ChatbotService with multi-provider support
    // ========================================================================

    // AI SDK implementation replaced manual provider handling

    // ========================================================================
    // FALLBACK RESPONSES — when no AI provider is available
    // ========================================================================

    private function getFallbackResponse(string $message, string $role): string
    {
        $message = mb_strtolower($message);

        $roleResponses = match ($role) {
            'admin' => [
                'siswa' => 'Untuk mengelola data siswa, buka menu **Students** di sidebar panel admin. Anda bisa menambah, mengedit, dan menghapus data siswa.',
                'guru' => 'Untuk mengelola data guru, buka menu **Teachers** di sidebar panel admin. Anda bisa mengatur spesialisasi dan status wali kelas.',
                'kelas' => 'Untuk mengelola kelas, buka menu **Classrooms** di sidebar. Anda bisa assign wali kelas dan mengatur tahun ajaran.',
                'laporan' => 'Laporan bisa diakses melalui menu masing-masing resource. Export data tersedia dalam format yang dibutuhkan.',
                'pengaturan' => 'Pengaturan sistem bisa diakses melalui menu **Shield** untuk roles & permissions, dan **Settings** untuk konfigurasi umum.',
                'role' => 'Manajemen roles dan permissions menggunakan Filament Shield. Buka menu **Shield** di sidebar untuk mengatur hak akses.',
            ],
            'guru' => [
                'jadwal' => 'Jadwal mengajar Anda bisa dilihat di menu **Schedules** pada panel admin.',
                'nilai' => 'Untuk menginput nilai, buka menu **Grades** di sidebar.',
                'presensi' => 'Data presensi siswa bisa dilihat di menu **Attendances**.',
                'kelas' => 'Data kelas perwalian bisa diakses di menu **Classrooms**.',
                'rapor' => 'E-Rapor bisa diakses di menu **E-Reports**.',
            ],
            'staff' => [
                'data' => 'Data master sekolah bisa dikelola melalui menu masing-masing di sidebar panel admin.',
                'surat' => 'Fitur surat-menyurat bisa diakses melalui menu administrasi.',
                'rekap' => 'Rekap data bisa diexport dari menu masing-masing resource.',
                'pengumuman' => 'Pengumuman bisa dibuat melalui menu **Notifications** di panel admin.',
            ],
            'orang_tua' => [
                'nilai' => 'Nilai anak Anda bisa dilihat di halaman **Dashboard Portal**.',
                'presensi' => 'Kehadiran anak Anda tercatat otomatis. Lihat rekap presensi di Dashboard Portal.',
                'rapor' => 'Rapor digital bisa diunduh di bagian **E-Raport Digital** pada Dashboard Portal.',
                'jadwal' => 'Jadwal pelajaran anak bisa dilihat di Dashboard Portal.',
                'guru' => 'Untuk menghubungi wali kelas, lihat informasi kontak di Dashboard Portal.',
            ],
            'siswa' => [
                'jadwal' => 'Jadwal pelajaran kamu bisa dilihat di halaman **Dashboard**.',
                'nilai' => 'Nilai kamu bisa dilihat di halaman **Dashboard**.',
                'presensi' => 'Data presensi kamu tercatat otomatis. Cek di Dashboard.',
                'rapor' => 'Rapor digital bisa diunduh di bagian **E-Raport Digital** di Dashboard.',
                'ekskul' => 'Informasi ekstrakurikuler bisa dilihat di halaman sekolah.',
            ],
            default => [],
        };

        $commonResponses = [
            'halo' => 'Halo! 👋 Ada yang bisa saya bantu?',
            'hai' => 'Hai! 👋 Silakan tanyakan apa saja tentang Aksara System!',
            'terima kasih' => 'Sama-sama! Senang bisa membantu. 😊',
            'bantuan' => 'Saya bisa membantu dengan pertanyaan seputar Aksara System. Coba tanyakan tentang fitur yang ingin Anda gunakan!',
        ];

        foreach ($roleResponses as $keyword => $reply) {
            if (str_contains($message, $keyword)) return $reply;
        }

        foreach ($commonResponses as $keyword => $reply) {
            if (str_contains($message, $keyword)) return $reply;
        }

        return match ($role) {
            'admin' => 'Sebagai admin, Anda bisa mengelola semua data sekolah melalui panel admin di /admin.',
            'guru' => 'Untuk bantuan tentang jadwal, nilai, atau presensi, silakan tanyakan secara spesifik.',
            'staff' => 'Untuk bantuan administrasi, silakan tanyakan tentang pengelolaan data yang Anda butuhkan.',
            'orang_tua' => 'Saya bisa membantu dengan informasi nilai, presensi, rapor, dan jadwal anak Anda.',
            'siswa' => 'Saya bisa membantu dengan info jadwal, nilai, presensi, dan rapor. Coba tanyakan lebih spesifik ya! 😊',
            default => 'Silakan tanyakan tentang fitur Aksara System yang ingin Anda gunakan.',
        };
    }
}
