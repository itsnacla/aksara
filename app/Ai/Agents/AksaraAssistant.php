<?php

namespace App\Ai\Agents;

use App\Ai\Middleware\LogAiTraffic;
use App\Ai\Tools\AnalyzeDropoutRisk;
use App\Ai\Tools\ClusterStudents;
use App\Ai\Tools\GetAbsentStudents;
use App\Ai\Tools\GetAcademicData;
use App\Ai\Tools\GetClassroomInfo;
use App\Ai\Tools\GetCocurricularData;
use App\Ai\Tools\GetExamSchedule;
use App\Ai\Tools\GetExtracurricularData;
use App\Ai\Tools\GetGraduatedStudents;
use App\Ai\Tools\GetLearningObjectives;
use App\Ai\Tools\GetP5Data;
use App\Ai\Tools\GetReportLink;
use App\Ai\Tools\GetScheduleData;
use App\Ai\Tools\GetSchoolSettings;
use App\Ai\Tools\GetStudentAnalytics;
use App\Ai\Tools\GetStudentDetails;
use App\Ai\Tools\GetStudentLeaves;
use App\Ai\Tools\GetSubjectsData;
use App\Ai\Tools\GetTeacherDirectory;
use App\Ai\Tools\GetTodaySchedule;
use App\Ai\Tools\SearchStudentByFilter;
use App\Models\ChatbotSetting;
use App\Models\User;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasMiddleware;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Laravel\Ai\Responses\AgentResponse;
use Stringable;

#[Provider([Lab::Gemini, Lab::OpenAI, Lab::Groq])] // Pure Failover Support
#[Model('gemini-2.0-flash')] // Default Model
#[Temperature(0.7)]
#[MaxSteps(5)] // Efficient tool usage - max 5 steps
#[Timeout(120)]
class AksaraAssistant implements Agent, Conversational, HasMiddleware, HasTools
{
    use Promptable {
        prompt as traitPrompt;
    }
    use RemembersConversations;

    protected ?string $activeModel = null;

    /**
     * Create a new agent instance.
     */
    public function __construct(public ?User $user = null)
    {
        $settings = ChatbotSetting::current();
        $this->activeModel = $settings->getModelFor($settings->provider ?: 'gemini');
    }

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $userRole = $this->user?->roles->first()?->name ?? 'guest';
        $userName = $this->user?->name ?? 'Pengguna';

        $baseInstructions =
            "🎓 IDENTITAS & PERAN:\n".
            'Anda adalah **Aksara AI**, asisten data akademik yang cerdas dan proaktif. '.
            'Tugas utama: LANGSUNG KASIH DATA yang diminta, bukan mengarahkan UI/navigasi. '.
            "Anda HANYA melayani Aksara System - tidak ada out-of-scope discussions.\n".
            "\n".
            "1. **STRICT FACTUALITY (NO HALLUCINATION)**:\n".
            "   • Anda HANYA BOLEH menjawab berdasarkan data riil dari database (didapat melalui eksekusi Tools).\n".
            "   • JANGAN PERNAH mengarang, menebak, atau menambahkan informasi yang tidak ada di sistem.\n".
            "   • Jika ditanya hal di luar data sekolah, tolak secara sopan: 'Saya hanya asisten AI Aksara dan hanya memiliki akses ke data internal sekolah.'\n".
            "\n".
            "2. **DATA FIRST, BUKAN UI**: \n".
            "   ✅ USER TANYA: 'Siapa yang bolos minggu ini?'\n".
            "   ✅ ANDA HARUS: Panggil GetAbsentStudents tool → Tampilkan tabel data\n".
            "   ❌ ANDA JANGAN: 'Silakan buka /admin → Attendances'\n".
            "\n".
            "3. **TOOL MATCHING & CHAINING (Tanpa ID / Pencarian Langsung)**:\n".
            "   • Jika ditanya nama (misal: 'Prediksi Budi'), otomatis gunakan tool dengan argumen nama tersebut. Tool sudah dirancang mencari dari nama langsung.\n".
            "   • 'Siapa yang bolos?' → GetAbsentStudents\n".
            "   • 'Siapa yang sakit/izin/cuti?' → GetStudentLeaves\n".
            "   • 'Jadwal hari ini?' → GetTodaySchedule\n".
            "   • 'Siswa mana yang lulus?' → GetGraduatedStudents\n".
            "   • 'Siapa guru Matematika?' atau 'Siapa wali kelas?' → GetTeacherDirectory\n".
            "   • 'Apa visi misi sekolah?' atau 'Apa NPSN?' → GetSchoolSettings\n".
            "   • 'Tema P5 kelas X?' atau 'Kelompok P5 saya?' → GetP5Data\n".
            "   • 'Berapa KKM Fisika?' → GetSubjectsData\n".
            "   • 'Top performer? Low performer?' → GetStudentAnalytics\n".
            "   • 'Prediksi risiko dropout Budi' → AnalyzeDropoutRisk\n".
            "   • 'Clustering kelas X IPA' → ClusterStudents\n".
            "   ALWAYS panggil tool dulu sebelum jawab!\n".
            "\n".
            "4. **FORMAT JAWABAN - STRUKTUR & VISUAL**:\n".
            "   • Gunakan **Tabel Markdown** untuk data terstruktur\n".

            "   • Langsung ke data, HINDARI basa-basi panjang\n".
            "   • Jika ada multiple entries: gunakan tabel, jangan list\n".
            "\n".
            "5. **ERROR HANDLING**:\n".
            "   • Tool returns no data? → '❌ Data tidak ditemukan di database. Pastikan penulisan nama sudah benar.'\n".
            "   • User tidak punya akses? → '🔒 Anda tidak memiliki akses untuk melihat data ini'\n".
            "   • Pertanyaan vague? → Tanya clarifying question, jangan asumsi\n".
            "\n".
            "🛡️ SECURITY & BOUNDARIES:\n".
            "- Hanya kasih data sesuai role/akses user (Tools sudah handle ini)\n".
            "- JANGAN memberi data siswa/guru tanpa otorisasi\n".
            "- JANGAN edit/create/delete (read-only tools)\n".
            "- Tolak topik di luar sistem akademik: 'Maaf, saya dikhususkan untuk akademik Aksara'\n".
            "\n".
            "🎯 ROLE-BASED BEHAVIOR:\n".
            $this->getRoleBasedContext($userRole, $userName).
            "\n".
            " RESPONSE QUALITY:\n".
            "- Akurasi: 100% (data dari tools = TRUTH, tidak ada spekulasi)\n".
            "- Kecepatan: Direct & concise, no fluff\n".
            "- Relevance: Hanya jawab yang ditanya, jangan extra\n".
            "- Max Steps: 5 (efficient execution)\n";

        return $baseInstructions;
    }

    /**
     * Get role-based context for richer instructions.
     */
    private function getRoleBasedContext(string $role, string $userName): string
    {
        return match (true) {
            str_contains(strtolower($role), 'admin') => "Role: ADMINISTRATOR (AKSES PENUH)\n".
                "• Bisa lihat: SEMUA data (siswa, guru, kelas, nilai, presensi, rapor, ekstrakurikuler)\n".
                "• Tools tersedia: Semua tools (GetAbsentStudents, GetGraduatedStudents, GetStudentAnalytics, dll)\n".
                "• Contoh pertanyaan yang LANGSUNG DIJAWAB dengan data:\n".
                "  - 'Siapa yang bolos bulan ini?' → GetAbsentStudents\n".
                "  - 'Siswa mana yang lulus?' → GetGraduatedStudents\n".
                "  - 'Ranking kelas X' → GetStudentAnalytics (class_ranking)\n".
                "  - 'Cari siswa bernama X' → SearchStudentByFilter\n".
                '• Response: Teknis, data-heavy, ringkas',

            str_contains(strtolower($role), 'guru') || str_contains(strtolower($role), 'teacher') => " Role: GURU (AKSES KELAS PERWALIAN)\n".
                "• Bisa lihat: Data siswa kelas perwalian SAJA (presensi, nilai, info siswa)\n".
                "• Tools: GetAbsentStudents (kelas sendiri), GetStudentAnalytics (kelas sendiri), GetClassroomInfo, GetTodaySchedule\n".
                "• Contoh pertanyaan:\n".
                "  - 'Siswa mana yang sering bolos?' → GetAbsentStudents (auto-filter kelas guru)\n".
                "  - 'Top performer kelas saya?' → GetStudentAnalytics (type=top_performers)\n".
                "  - 'Jadwal hari ini?' → GetTodaySchedule\n".
                "  - 'Daftar siswa kelas saya' → GetClassroomInfo\n".
                "  - 'Siapa saja yang izin/sakit kelas saya?' → GetStudentLeaves\n".
                "  - 'Daftar kelompok P5' → GetP5Data\n".
                "  - 'Cari kontak guru' → GetTeacherDirectory\n".
                '• Response: Profesional, supportif, fokus pembelajaran',

            str_contains(strtolower($role), 'staff') => "👔 Role: STAFF/TU (AKSES DATA MASTER)\n".
                "• Bisa lihat: Data master, laporan agregat, presensi/nilai semua kelas (tidak detail siswa)\n".
                "• Tools: SearchStudentByFilter, GetStudentAnalytics, GetGraduatedStudents, GetExamSchedule\n".
                "• Contoh pertanyaan:\n".
                "  - 'Cari siswa NISN 12345' → SearchStudentByFilter\n".
                "  - 'Siswa yang sudah lulus' → GetGraduatedStudents\n".
                "  - 'Jadwal ujian' → GetExamSchedule\n".
                '• Response: Praktis, administratif, clear data structure',

            str_contains(strtolower($role), 'orang_tua') || str_contains(strtolower($role), 'parent') || str_contains(strtolower($role), 'wali') => "👨‍👩‍👧 Role: ORANG TUA (AKSES ANAK SENDIRI)\n".
                "• Bisa lihat: HANYA data anak sendiri (nilai, presensi, jadwal, rapor)\n".
                "• Tools: GetAcademicData (anak), GetTodaySchedule (anak), GetReportLink (anak)\n".
                "• Contoh pertanyaan:\n".
                "  - 'Berapa nilai anak saya?' → GetAcademicData\n".
                "  - 'Jadwal hari ini untuk anak saya?' → GetTodaySchedule\n".
                "  - 'Download rapor' → GetReportLink\n".
                "  - 'Status pengajuan izin anak saya?' → GetStudentLeaves\n".
                "  - 'Apa kelompok P5 anak saya?' → GetP5Data\n".
                '• Response: Ramah, mudah dipahami, data anak-focused',

            str_contains(strtolower($role), 'siswa') || str_contains(strtolower($role), 'student') => "👨‍🎓 Role: SISWA (AKSES DIRI SENDIRI)\n".
                "• Bisa lihat: HANYA data diri sendiri (jadwal, nilai, presensi, rapor)\n".
                "• Tools: GetStudentDetails (own), GetAcademicData (own), GetTodaySchedule, GetReportLink\n".
                "• Contoh pertanyaan:\n".
                "  - 'Jadwal aku hari ini apa aja?' → GetTodaySchedule\n".
                "  - 'Nilai saya berapa?' → GetAcademicData\n".
                "  - 'Download rapor' → GetReportLink\n".
                "  - 'Apa kelompok P5 saya?' → GetP5Data\n".
                "  - 'Apakah izin sakit saya di-approve?' → GetStudentLeaves\n".
                "  - 'Siapa nama wali kelas/kepala sekolah?' → GetTeacherDirectory\n".
                '• Response: Santai, ramah, bahasa anak muda',

            default => "Role: GUEST (NO ACCESS)\n".
                "• Tidak boleh lihat data siswa/guru\n".
                "• Hanya bisa tanya info umum sekolah\n".
                '• Response: Ramah, tawarkan login'
        };
    }

    /**
     * Get the tools available to the agent.
     */
    public function tools(): iterable
    {
        return [
            //  CORE ACADEMIC TOOLS
            new GetStudentDetails($this->user),
            new GetAcademicData($this->user),
            new GetScheduleData($this->user),
            new GetReportLink($this->user),

            // 📈 ANALYTICS & SPECIFIC QUERIES
            new GetAbsentStudents($this->user),          // "Siapa yang bolos?"
            new GetGraduatedStudents($this->user),       // "Siapa yang lulus?"
            new GetTodaySchedule($this->user),           // "Jadwal hari ini?"
            new GetStudentAnalytics($this->user),        // "Top performer? Ranking?"

            // 🔍 EXTENDED DATA TOOLS
            new GetClassroomInfo($this->user),
            new GetExtracurricularData($this->user),
            new GetCocurricularData($this->user),
            new GetExamSchedule($this->user),
            new GetLearningObjectives($this->user),
            new SearchStudentByFilter($this->user),
            new GetSubjectsData($this->user),
            new GetStudentLeaves($this->user),

            // 🏫 MASTER DATA & SCHOOL KNOWLEDGE
            new GetSchoolSettings($this->user),          // "Visi Misi, NPSN"
            new GetTeacherDirectory($this->user),        // "Cari guru/wali kelas"
            new GetP5Data($this->user),                  // "Data kelompok P5 Kurikulum Merdeka"

            // 🤖 DATA SCIENCE & PREDICTIVE TOOLS
            new AnalyzeDropoutRisk($this->user),         // "Prediksi risiko dropout"
            new ClusterStudents($this->user),            // "Cluster belajar siswa di kelas X"
        ];
    }

    /**
     * Get the agent's middleware.
     */
    public function middleware(): array
    {
        return [
            new LogAiTraffic,
        ];
    }

    /**
     * Override prompt to use dynamic model and provider failover.
     */
    public function prompt(
        string $prompt,
        array $attachments = [],
        Lab|array|string|null $provider = null,
        ?string $model = null,
        ?int $timeout = null
    ): AgentResponse {
        // If a specific provider and model are explicitly passed, use them directly
        if ($provider && $model) {
            return $this->traitPrompt($prompt, $attachments, $provider, $model, $timeout);
        }

        $settings = ChatbotSetting::current();
        
        $primaryProvider = $settings->primary_provider ?: 'gemini';
        $fallbackList = $settings->getFallbackProvidersArray();
        
        // Build the sequence of providers to try
        $providersToTry = array_unique(array_merge([$primaryProvider], $fallbackList));
        
        $lastException = null;
        
        foreach ($providersToTry as $prov) {
            // Check if API key is configured for this provider in DB or config/env
            $apiKey = $settings->getApiKeyFor($prov) ?: config("ai.providers.{$prov}.key");
            if (empty($apiKey)) {
                // Skip if no API key is configured
                continue;
            }
            
            $provModel = $settings->getModelFor($prov);
            
            $lab = match ($prov) {
                'gemini', 'google' => Lab::Gemini,
                'openai' => Lab::OpenAI,
                'groq' => Lab::Groq,
                'anthropic' => Lab::Anthropic,
                default => $prov,
            };
            
            try {
                // Try executing the prompt with the specific provider and model
                return $this->traitPrompt($prompt, $attachments, $lab, $provModel, $timeout);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Aksara Chatbot - Provider {$prov} failed: " . $e->getMessage());
                $lastException = $e;
            }
        }
        
        // If all providers fail, throw the last exception
        throw $lastException ?: new \Exception("No configured AI providers succeeded.");
    }
}
