<?php

namespace App\Ai\Examples;

/**
 * AKSARA AI - DATA-FIRST EXAMPLES
 *
 * Ini adalah contoh-contoh bagaimana AI sekarang bekerja dengan approach "data-first"
 * BUKAN mengarahkan ke UI, tapi langsung memberikan data yang diminta.
 */
class DataFirstExamples
{
    /**
     * EXAMPLE 1: Admin menanya "Siapa yang bolos minggu ini?"
     *
     * OLD (WRONG):
     *   AI: "Silakan buka /admin → Attendances untuk melihat data bolos siswa"
     *
     * NEW (CORRECT):
     *   1. AI detect: "bolos" → matching pattern "absent"
     *   2. AI call: GetAbsentStudents(['period' => 'week'])
     *   3. AI format result sebagai tabel:
     *
     *      📋 BOLOS MINGGU INI
     *
     *      | Kelas | Jumlah | Nama Siswa | Tanggal |
     *      |-------|--------|-----------|---------|
     *      | X-A   | 2      | Adi, Budi | 20-05   |
     *      | X-B   | 1      | Citra     | 21-05   |
     */
    public static function exampleAbsentStudents()
    {
        return [
            'user_message' => 'Siapa yang bolos minggu ini?',
            'ai_steps' => [
                '1. Parse keyword: "bolos" = absent',
                '2. Determine tool: GetAbsentStudents',
                '3. Call: GetAbsentStudents(period="week")',
                '4. Format: Tabel by class',
                '5. Return: Direct data, no UI navigation',
            ],
            'expected_response' => '📋 **Bolos Minggu Ini** | Kelas | Siswa | Tanggal |...',
        ];
    }

    /**
     * EXAMPLE 2: Guru menanya "Jadwal hari ini apa aja?"
     *
     * OLD (WRONG):
     *   AI: "Silakan buka /admin → Schedules untuk melihat jadwal Anda"
     *
     * NEW (CORRECT):
     *   1. AI detect: "jadwal", "hari ini"
     *   2. AI call: GetTodaySchedule()
     *   3. AI format hasil:
     *
     *      🗓️ JADWAL HARI INI (Senin, 26-05-2025)
     *
     *      | Jam | Mapel | Kelas | Ruangan |
     *      |-----|-------|-------|---------|
     *      | 07:00-08:30 | Matematika | X-A | R.10 |
     *      | 08:30-10:00 | Bahasa | X-B | R.11 |
     */
    public static function exampleTodaySchedule()
    {
        return [
            'user_message' => 'Jadwal hari ini apa aja?',
            'ai_steps' => [
                '1. Recognize: "jadwal hari ini" = today schedule query',
                '2. Determine tool: GetTodaySchedule',
                '3. Call: GetTodaySchedule() [auto-use current day]',
                '4. Parse day → get day_name (Senin/Selasa/etc)',
                '5. Format: Tabel dengan jam, mapel, kelas, ruangan',
            ],
            'expected_response' => '🗓️ **Jadwal Hari Ini** (Senin, 26-05) | 07:00-08:30 | Matematika | X-A | R.10',
        ];
    }

    /**
     * EXAMPLE 3: Admin menanya "Siswa mana yang sudah lulus?"
     *
     * OLD (WRONG):
     *   AI: "Buka /admin → Students dan filter status 'Lulus'"
     *
     * NEW (CORRECT):
     *   1. AI detect: "lulus" = graduated
     *   2. AI call: GetGraduatedStudents()
     *   3. AI format:
     *
     *      🎓 SISWA YANG LULUS
     *
     *      | Nama | NISN | Kelas Terakhir | Tahun Lulus |
     *      |------|------|----------------|------------|
     *      | Adi Pratama | 1234567890 | XII-IPA-1 | 2025 |
     */
    public static function exampleGraduatedStudents()
    {
        return [
            'user_message' => 'Siswa mana yang sudah lulus?',
            'ai_steps' => [
                '1. Parse: "lulus" = graduated status',
                '2. Tool: GetGraduatedStudents()',
                '3. Call with filters: year=current (optional)',
                '4. Group by: tahun_lulus',
                '5. Format: Tabel dengan nama, NISN, kelas, tahun',
            ],
            'expected_response' => '🎓 **Siswa Lulus** | Adi Pratama | 1234567890 | XII-IPA | 2025 |...',
        ];
    }

    /**
     * EXAMPLE 4: Guru menanya "Siapa yang paling pintar di kelas saya?"
     *
     * OLD (WRONG):
     *   AI: "Lihat Grades dan hitung rata-rata di /admin → Grades"
     *
     * NEW (CORRECT):
     *   1. AI detect: "paling pintar" = top_performers
     *   2. AI call: GetStudentAnalytics(['type' => 'top_performers', 'study_group_id' => teacher.class_id])
     *   3. AI format:
     *
     *      ⭐ TOP PERFORMER KELAS
     *
     *      | Ranking | Nama | NISN | Rata-rata Nilai |
     *      |---------|------|------|-----------------|
     *      | 1 | Citra Dewi | 9876543210 | 92.50 |
     *      | 2 | Budi Santoso | 9876543211 | 88.75 |
     */
    public static function exampleTopPerformers()
    {
        return [
            'user_message' => 'Siapa top performer di kelas saya?',
            'ai_steps' => [
                '1. Parse: "paling pintar" / "top performer"',
                '2. Tool: GetStudentAnalytics',
                '3. Call: type="top_performers", study_group_id=auto_fetch_from_teacher',
                '4. Format: Ranking table',
                '5. Include: Nama, NISN, rata-rata nilai',
            ],
            'expected_response' => '⭐ **Top Performer** | 1. Citra Dewi | 92.50 | 2. Budi Santoso | 88.75 |',
        ];
    }

    /**
     * EXAMPLE 5: Admin menanya "Cari siswa bernama Adi"
     *
     * OLD (WRONG):
     *   AI: "Silakan gunakan fitur search di /admin → Students"
     *
     * NEW (CORRECT):
     *   1. AI detect: "cari", "Adi"
     *   2. AI call: SearchStudentByFilter(['name' => 'Adi', 'limit' => 10])
     *   3. AI format:
     *
     *      🔍 HASIL PENCARIAN: "Adi"
     *
     *      | Nama | NISN | Kelas | Level |
     *      |------|------|-------|-------|
     *      | Adi Pratama | 1234567890 | X-A | Kelas 10 |
     *      | Adi Wijaya | 1234567891 | IX-B | Kelas 9 |
     */
    public static function exampleSearchStudent()
    {
        return [
            'user_message' => 'Cari siswa bernama Adi',
            'ai_steps' => [
                '1. Parse: Extract name "Adi"',
                '2. Tool: SearchStudentByFilter',
                '3. Call: name="Adi", limit=20',
                '4. Return: List dengan NISN, kelas, level',
                '5. Format: Tabel hasil pencarian',
            ],
            'expected_response' => '🔍 **Hasil Pencarian: Adi** | Adi Pratama | 1234567890 | X-A |...',
        ];
    }

    /**
     * EXAMPLE 6: Orang tua menanya "Berapa nilai anak saya?"
     *
     * OLD (WRONG):
     *   AI: "Buka /dashboard → Nilai untuk melihat nilai anak"
     *
     * NEW (CORRECT):
     *   1. AI detect: "nilai anak"
     *   2. AI auto-identify: User is parent → get child_id from context
     *   3. AI call: GetAcademicData(['student_id' => child_id])
     *   4. AI format:
     *
     *      📊 NILAI ANAK ANDA
     *
     *      | Mapel | Tugas | UTS | UAS | Rata-rata |
     *      |-------|-------|-----|-----|-----------|
     *      | Matematika | 85 | 80 | 82 | 82.33 |
     *      | Bahasa | 90 | 88 | 89 | 89.00 |
     */
    public static function exampleParentViewGrades()
    {
        return [
            'user_message' => 'Berapa nilai anak saya?',
            'ai_steps' => [
                '1. Detect: "nilai anak"',
                '2. Security: Verify parent role',
                '3. Auto-fetch: child_id dari parent.students',
                '4. Tool: GetAcademicData(student_id=child_id)',
                '5. Format: Tabel nilai per mapel',
            ],
            'expected_response' => '📊 **Nilai Anak** | Matematika: 85,80,82 | Bahasa: 90,88,89 |',
        ];
    }

    /**
     * EXAMPLE 7: Siswa menanya "Jadwal saya hari ini?"
     *
     * OLD (WRONG):
     *   AI: "Silakan buka /dashboard → Jadwal untuk melihat jadwal pelajaran Anda"
     *
     * NEW (CORRECT):
     *   1. AI detect: "jadwal", "hari ini"
     *   2. AI auto-identify: User is student → get study_group from current enrollment
     *   3. AI call: GetTodaySchedule() [auto-filter by student class]
     *   4. AI format:
     *
     *      JADWAL KU HARI INI
     *
     *      | Jam | Mapel | Guru | Ruangan |
     *      |-----|-------|------|---------|
     *      | 07:00-08:30 | Matematika | Pak Ahmad | R.10 |
     *      | 10:00-11:30 | Olahraga | Bu Siti | Lapangan |
     */
    public static function exampleStudentSchedule()
    {
        return [
            'user_message' => 'Jadwal saya hari ini apa aja?',
            'ai_steps' => [
                '1. Detect: "jadwal", "hari ini"',
                '2. Role: Student',
                '3. Auto-fetch: current class from student.current_study_group',
                '4. Tool: GetTodaySchedule() [auto-filter by class]',
                '5. Format: Simple, friendly table untuk siswa',
            ],
            'expected_response' => '**Jadwal Ku Hari Ini** | 07:00: Matematika (Pak Ahmad) | 10:00: Olahraga (Bu Siti)',
        ];
    }

    /**
     * KEY PRINCIPLES FOR ALL EXAMPLES:
     *
     * ✅ DO:
     *   - Parse user message untuk extract intent
     *   - Matching ke tool yang paling relevan
     *   - CALL TOOL DULU sebelum jawab
     *   - Format output dengan tabel/list yang rapi
     *   - Include emoji yang appropriate
     *   - Auto-filter berdasarkan role/context
     *   - Direct answer, no UI navigation
     *
     * ❌ DON'T:
     *   - Mengarahkan ke UI/navbar
     *   - Menebak data tanpa call tool
     *   - Memberikan data yang user tidak punya akses
     *   - Long-winded explanations
     *   - Suggestion navigasi alih-alih data
     */
}
