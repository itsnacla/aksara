<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    /**
     * Handle an incoming chat message and return an AI response.
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array',
            'history.*.role' => 'in:user,model',
            'history.*.text' => 'string|max:2000',
        ]);

        $userMessage = $request->input('message');
        $history = $request->input('history', []);
        $user = $request->user();

        try {
            $reply = $this->getAIResponse($userMessage, $history, $user);
        } catch (\Exception $e) {
            Log::error('Chatbot AI error: ' . $e->getMessage());
            $reply = $this->getFallbackResponse($userMessage, $this->getUserRole($user));
        }

        return response()->json([
            'reply' => $reply,
        ]);
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

        // Map Spatie role names to chatbot role keys
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

    /**
     * Get display name for a role.
     */
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

    /**
     * Get role-specific greeting message.
     */
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

    /**
     * Get role-specific quick action chips.
     */
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

    /**
     * Build role-specific system instruction for the AI.
     */
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

            'orang_tua' => "Pengguna ini adalah Orang Tua/Wali Murid. " .
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

        return $base . $roleContext;
    }

    /**
     * Get response from AI provider (Google Gemini).
     */
    private function getAIResponse(string $message, array $history, $user): string
    {
        $apiKey = config('services.gemini.api_key');
        $role = $this->getUserRole($user);

        if (empty($apiKey)) {
            return $this->getFallbackResponse($message, $role);
        }

        $systemInstruction = $this->buildSystemInstruction($role, $user);

        // Build Gemini API conversation format
        $contents = [];

        foreach ($history as $entry) {
            $contents[] = [
                'role' => $entry['role'] === 'user' ? 'user' : 'model',
                'parts' => [['text' => $entry['text']]],
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $message]],
        ];

        $response = Http::timeout(30)->post(
            "https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent?key={$apiKey}",
            [
                'system_instruction' => [
                    'parts' => [['text' => $systemInstruction]],
                ],
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 2048,
                ],
            ]
        );

        if ($response->successful()) {
            $data = $response->json();
            return $data['candidates'][0]['content']['parts'][0]['text']
                ?? 'Maaf, saya tidak bisa memproses permintaan Anda saat ini.';
        }

        Log::warning('Gemini API error', ['status' => $response->status(), 'body' => $response->body()]);

        // Give clear message on rate limit
        if ($response->status() === 429) {
            return 'Maaf, AI sedang sibuk karena terlalu banyak permintaan. Silakan coba lagi dalam beberapa detik ya! ⏳';
        }

        return $this->getFallbackResponse($message, $role);
    }

    /**
     * Role-specific fallback responses when no AI API key is configured.
     */
    private function getFallbackResponse(string $message, string $role): string
    {
        $message = mb_strtolower($message);

        // Role-specific responses
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
                'jadwal' => 'Jadwal mengajar Anda bisa dilihat di menu **Schedules** pada panel admin. Jadwal diatur per semester oleh bagian kurikulum.',
                'nilai' => 'Untuk menginput nilai, buka menu **Grades** di sidebar. Pilih mata pelajaran dan kelas, kemudian input nilai per siswa.',
                'presensi' => 'Data presensi siswa bisa dilihat di menu **Attendances**. Anda bisa memfilter berdasarkan kelas dan tanggal.',
                'kelas' => 'Jika Anda wali kelas, data kelas perwalian bisa diakses di menu **Classrooms**. Anda bisa melihat daftar siswa di kelas Anda.',
                'rapor' => 'E-Rapor bisa diakses di menu **E-Reports**. Sebagai guru, Anda bisa meninjau dan menyetujui rapor siswa.',
            ],
            'staff' => [
                'data' => 'Data master sekolah (siswa, guru, kelas) bisa dikelola melalui menu masing-masing di sidebar panel admin.',
                'surat' => 'Fitur surat-menyurat bisa diakses melalui menu administrasi. Anda bisa membuat dan mengelola surat keluar/masuk.',
                'rekap' => 'Rekap data presensi dan nilai bisa diexport dari menu masing-masing resource di panel admin.',
                'pengumuman' => 'Pengumuman bisa dibuat melalui menu **Notifications** di panel admin. Pilih target penerima sesuai kebutuhan.',
            ],
            'orang_tua' => [
                'nilai' => 'Nilai anak Anda bisa dilihat di halaman **Dashboard Portal**. Klik menu Nilai untuk melihat detail per mata pelajaran.',
                'presensi' => 'Kehadiran anak Anda tercatat otomatis. Lihat rekap presensi di halaman Dashboard Portal.',
                'rapor' => 'Rapor digital anak Anda bisa diunduh di bagian **E-Raport Digital** pada Dashboard Portal.',
                'jadwal' => 'Jadwal pelajaran anak Anda bisa dilihat di halaman Dashboard Portal. Jadwal diperbarui setiap awal semester.',
                'guru' => 'Untuk menghubungi wali kelas, silakan lihat informasi kontak di halaman Dashboard Portal.',
            ],
            'siswa' => [
                'jadwal' => 'Jadwal pelajaran kamu bisa dilihat di halaman **Dashboard**. Jadwal diperbarui setiap awal semester.',
                'nilai' => 'Nilai kamu bisa dilihat di halaman **Dashboard**. Klik bagian Nilai untuk lihat detail per mata pelajaran.',
                'presensi' => 'Data presensi kamu tercatat otomatis. Cek rekap kehadiran di halaman Dashboard.',
                'rapor' => 'Rapor digital bisa diunduh di bagian **E-Raport Digital** di Dashboard. Kamu bisa unduh rapor semester terakhir.',
                'ekskul' => 'Informasi ekstrakurikuler bisa dilihat di halaman sekolah. Hubungi guru pembina untuk info pendaftaran.',
            ],
            default => [],
        };

        // Common responses for all roles
        $commonResponses = [
            'halo' => 'Halo! 👋 Ada yang bisa saya bantu?',
            'hai' => 'Hai! 👋 Silakan tanyakan apa saja tentang Aksara System!',
            'terima kasih' => 'Sama-sama! Senang bisa membantu. Jangan ragu untuk bertanya lagi ya! 😊',
            'bantuan' => 'Saya bisa membantu dengan pertanyaan seputar penggunaan Aksara System. Coba tanyakan tentang fitur yang ingin Anda gunakan!',
        ];

        // Check role-specific responses first
        foreach ($roleResponses as $keyword => $reply) {
            if (str_contains($message, $keyword)) {
                return $reply;
            }
        }

        // Then check common responses
        foreach ($commonResponses as $keyword => $reply) {
            if (str_contains($message, $keyword)) {
                return $reply;
            }
        }

        // Default fallback per role
        return match ($role) {
            'admin' => 'Terima kasih atas pertanyaan Anda! Sebagai admin, Anda bisa mengelola semua data sekolah melalui panel admin di /admin. Untuk bantuan spesifik, coba tanyakan tentang fitur yang ingin Anda gunakan.',
            'guru' => 'Terima kasih atas pertanyaan Anda, Bapak/Ibu Guru! Untuk bantuan lebih lanjut tentang jadwal mengajar, nilai, atau presensi, silakan tanyakan secara spesifik.',
            'staff' => 'Terima kasih atas pertanyaan Anda! Untuk bantuan administrasi, silakan tanyakan tentang pengelolaan data, surat, atau rekap yang Anda butuhkan.',
            'orang_tua' => 'Terima kasih atas pertanyaan Anda, Bapak/Ibu! Saya bisa membantu dengan informasi nilai, presensi, rapor, dan jadwal anak Anda. Silakan tanyakan secara spesifik.',
            'siswa' => 'Terima kasih atas pertanyaanmu! Saya bisa membantu dengan info jadwal, nilai, presensi, dan rapor. Coba tanyakan lebih spesifik ya! 😊',
            default => 'Terima kasih atas pertanyaan Anda! Silakan tanyakan tentang fitur Aksara System yang ingin Anda gunakan.',
        };
    }
}
