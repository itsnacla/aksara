<?php

namespace App\Ai;

/**
 * AksaraKnowledgeBase - Static knowledge base untuk Aksara AI.
 * Berisi FAQ, feature documentation, dan system guidelines yang dapat
 * di-inject ke dalam prompts untuk memberikan konteks yang lebih kaya.
 */
class AksaraKnowledgeBase
{
    /**
     * Frequently Asked Questions dan Jawaban
     */
    public const FAQS = [
        'tentang_rapor' => [
            'q' => 'Apa itu rapor? Bagaimana cara mengunduh rapor?',
            'a' => 'Rapor adalah laporan akademik siswa yang berisi nilai akhir semester dan prestasi. ' .
                   'Siswa dan orang tua dapat mengunduh rapor digital di portal /dashboard → Rapor. ' .
                   'Guru dapat membuat rapor melalui /admin → Rapor atau fitur grading.',
        ],
        'tentang_presensi' => [
            'q' => 'Bagaimana cara melihat presensi dan absensi?',
            'a' => 'Siswa: Lihat di /dashboard → Presensi. ' .
                   'Orang tua: Monitor presensi anak di /dashboard → Presensi Anak. ' .
                   'Guru: Input presensi di /admin → Attendance atau via fitur mobile attendance. ' .
                   'Presensi dihitung per pertemuan dengan status Hadir, Sakit, Izin, atau Alfa.',
        ],
        'tentang_nilai' => [
            'q' => 'Bagaimana cara menginput dan melihat nilai siswa?',
            'a' => 'Guru: Input nilai di /admin → Grades (Tugas, UTS, UAS). ' .
                   'Siswa: Lihat nilai di /dashboard → Nilai. ' .
                   'Orang tua: Monitor nilai anak di /dashboard → Nilai Anak. ' .
                   'Nilai menggunakan skala 0-100 dan dihitung menjadi nilai rapor dengan rumus tertentu.',
        ],
        'tentang_jadwal' => [
            'q' => 'Bagaimana cara melihat jadwal pelajaran?',
            'a' => 'Siswa: Lihat jadwal di /dashboard → Jadwal atau /admin → Jadwal Pelajaran. ' .
                   'Guru: Lihat jadwal mengajar di /admin → Jadwal Mengajar. ' .
                   'Jadwal berisi mata pelajaran, jam mulai-selesai, hari, ruangan, dan guru pengajar.',
        ],
        'tentang_ekstrakurikuler' => [
            'q' => 'Apa itu ekstrakurikuler dan bagaimana cara mendaftar?',
            'a' => 'Ekstrakurikuler adalah kegiatan akademik tambahan (olahraga, seni, dll). ' .
                   'Admin/guru dapat mengelola ekstrakurikuler di /admin → Ekstrakurikuler. ' .
                   'Siswa dapat melihat daftar ekstrakurikuler dan nilainya (jika ada) di /dashboard → Ekstrakurikuler.',
        ],
        'tentang_wali_kelas' => [
            'q' => 'Apa peran wali kelas?',
            'a' => 'Wali kelas adalah guru yang bertanggung jawab atas satu kelas. ' .
                   'Tugas: Input presensi, monitor nilai, berkomunikasi dengan orang tua, mengelola siswa di kelas. ' .
                   'Setiap kelas (rombel) hanya memiliki satu wali kelas yang ditentukan admin.',
        ],
        'tentang_login_as' => [
            'q' => 'Apa itu fitur Login As?',
            'a' => 'Login As memungkinkan admin melihat sistem dari perspektif user lain (siswa, guru, orang tua). ' .
                   'Caranya: /admin → Login As → Pilih user → Login. Berguna untuk testing dan support.',
        ],
        'tentang_buku_induk' => [
            'q' => 'Apa itu Buku Induk?',
            'a' => 'Buku Induk adalah dokumen lengkap data siswa sejak mendaftar hingga lulus. ' .
                   'Berisi: biodata, orang tua, riwayat kelas, prestasi, absensi, catatan khusus. ' .
                   'Dapat di-generate sebagai PDF untuk arsip resmi sekolah.',
        ],
    ];

    /**
     * Feature Overview - Daftar fitur Aksara
     */
    public const FEATURES = [
        'student_management' => [
            'name' => 'Manajemen Siswa',
            'description' => 'CRUD data siswa, NISN, biodata, orang tua, riwayat kelas',
            'access_path' => '/admin → Siswa atau /admin → Buku Induk',
        ],
        'teacher_management' => [
            'name' => 'Manajemen Guru',
            'description' => 'CRUD data guru, NIP, mata pelajaran, kelas perwalian',
            'access_path' => '/admin → Guru',
        ],
        'classroom_management' => [
            'name' => 'Manajemen Kelas',
            'description' => 'Buat/edit rombel, assign wali kelas, kelola siswa per kelas',
            'access_path' => '/admin → Classrooms atau Jadwal Mengajar',
        ],
        'schedule_management' => [
            'name' => 'Jadwal Pelajaran',
            'description' => 'Buat jadwal mengajar, auto-generate dengan constraints (guru conflict, etc)',
            'access_path' => '/admin → Jadwal Mengajar',
        ],
        'grading' => [
            'name' => 'Grading / Nilai',
            'description' => 'Input nilai tugas, UTS, UAS per siswa per mapel, generate rapor',
            'access_path' => '/admin → Grades atau Student Grading Filament',
        ],
        'attendance' => [
            'name' => 'Presensi',
            'description' => 'Input presensi siswa per pertemuan, tracking absensi, laporan kehadiran',
            'access_path' => '/admin → Attendances',
        ],
        'extracurricular' => [
            'name' => 'Ekstrakurikuler',
            'description' => 'Kelola ekstrakurikuler, nilai ekstrakurikuler siswa',
            'access_path' => '/admin → Ekstrakurikuler',
        ],
        'reports' => [
            'name' => 'Laporan',
            'description' => 'Generate rapor, buku induk, laporan presensi, laporan nilai, laporan jadwal',
            'access_path' => '/admin → Reports',
        ],
        'broadcast' => [
            'name' => 'Broadcast / Pengumuman',
            'description' => 'Kirim pengumuman ke siswa, guru, atau semua user',
            'access_path' => '/admin → Broadcast',
        ],
        'chatbot_settings' => [
            'name' => 'Pengaturan Chatbot',
            'description' => 'Konfigurasi AI provider, model, fallback providers, custom settings',
            'access_path' => '/admin → Chatbot Settings',
        ],
    ];

    /**
     * System Guidelines - Panduan teknis sistem
     */
    public const GUIDELINES = [
        'data_hierarchy' => [
            'name' => 'Hierarki Data',
            'content' => 'Akademik Tahun → Level (Tingkatan) → StudyGroup (Rombel) → Siswa. ' .
                         'Setiap siswa hanya bisa dalam 1 rombel per tahun ajaran.',
        ],
        'grading_scale' => [
            'name' => 'Skala Penilaian',
            'content' => 'Skala nilai 0-100. Kategori: 0-40=Kurang, 41-60=Cukup, 61-80=Baik, 81-100=Sangat Baik. ' .
                         'Nilai rapor adalah rata-rata dari Tugas (25%), UTS (35%), UAS (40%) atau disesuaikan kebijakan sekolah.',
        ],
        'attendance_status' => [
            'name' => 'Status Presensi',
            'content' => 'Hadir (H), Sakit (S), Izin (I), Alfa/Tanpa Keterangan (A). ' .
                         'Syarat kelulusan biasanya: Kehadiran ≥ 80%. Sakit dan Izin tidak mengurangi nilai.',
        ],
        'academic_year_management' => [
            'name' => 'Manajemen Tahun Ajaran',
            'content' => 'Tahun ajaran format: "2024/2025". Aktifkan 1 tahun ajaran per waktu. ' .
                         'Saat tahun ajaran berakhir, kelas naik level (auto-promotion atau manual). ' .
                         'Data semester 1 dan 2 disimpan terpisah dalam database.',
        ],
        'schedule_auto_generation' => [
            'name' => 'Auto-Generate Jadwal',
            'content' => 'Fitur auto-generate membuat jadwal pelajaran otomatis dengan constraints: ' .
                         'Tidak ada guru double, tidak ada rombel double, setiap mapel ada guru-nya, ' .
                         'jam tidak bentrok, prefer guru expertise. Masih bisa edit manual jika perlu.',
        ],
    ];

    /**
     * Retrieve semua FAQ
     */
    public static function getFaqs(): array
    {
        return self::FAQS;
    }

    /**
     * Search FAQ berdasarkan keyword
     */
    public static function searchFaq(string $keyword): ?array
    {
        $keyword = strtolower($keyword);
        foreach (self::FAQS as $key => $faq) {
            if (strpos(strtolower($faq['q']), $keyword) !== false ||
                strpos(strtolower($faq['a']), $keyword) !== false) {
                return $faq;
            }
        }
        return null;
    }

    /**
     * Retrieve semua fitur
     */
    public static function getFeatures(): array
    {
        return self::FEATURES;
    }

    /**
     * Retrieve semua guidelines
     */
    public static function getGuidelines(): array
    {
        return self::GUIDELINES;
    }

    /**
     * Build konteks untuk injection ke prompt
     */
    public static function buildContextualPrompt(string $userQuery): string
    {
        $context = "📚 AKSARA KNOWLEDGE BASE:\n\n";

        // Search relevant FAQ
        $faq = self::searchFaq($userQuery);
        if ($faq) {
            $context .= "Relevant FAQ:\nQ: {$faq['q']}\nA: {$faq['a']}\n\n";
        }

        // Add system guidelines
        $context .= "Panduan Sistem:\n";
        foreach (self::GUIDELINES as $guide) {
            $context .= "• {$guide['name']}: {$guide['content']}\n";
        }

        return $context;
    }

    /**
     * Dapatkan tips untuk role tertentu
     */
    public static function getRoleTips(string $role): string
    {
        return match (true) {
            str_contains(strtolower($role), 'admin') =>
                "TIPS ADMIN: Gunakan /admin → Dashboard untuk overview. " .
                "Setup data master dulu (Tahun Ajaran, Level, Guru, Ruangan) sebelum bikin kelas. " .
                "Gunakan auto-schedule generator untuk efisiensi. " .
                "Monitor broadcast dan chat logs untuk QA.",

            str_contains(strtolower($role), 'guru') =>
                "TIPS GURU: Lihat jadwal mengajar Anda di /admin → Jadwal Mengajar. " .
                "Input presensi siswa setiap pertemuan di Attendance. " .
                "Input nilai di Grades, bisa bulk-upload atau manual. " .
                "Download daftar siswa kelas di buku induk untuk referensi offline.",

            str_contains(strtolower($role), 'orang_tua') =>
                "TIPS ORANG TUA: Login ke /dashboard dengan akun Anda. " .
                "Monitor nilai anak di 'Nilai'. " .
                "Cek presensi anak setiap bulan. " .
                "Download rapor anak di 'Rapor' untuk arsip keluarga. " .
                "Hubungi wali kelas via chat jika ada masalah akademik.",

            str_contains(strtolower($role), 'siswa') =>
                "TIPS SISWA: Lihat jadwal pelajaran di /dashboard → Jadwal. " .
                "Monitor nilai Anda secara berkala. " .
                "Jangan sampai absensi tinggi (target ≥80% kehadiran). " .
                "Lihat rapor semester di 'Rapor' untuk evaluasi diri. " .
                "Ikuti ekstrakurikuler untuk pengembangan bakat.",

            default => "Gunakan chat ini untuk pertanyaan akademik dan sistem Aksara."
        };
    }
}
