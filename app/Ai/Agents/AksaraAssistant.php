<?php

namespace App\Ai\Agents;

use App\Ai\Middleware\LogAiTraffic;
use App\Ai\Tools\GetAcademicData;
use App\Ai\Tools\GetReportLink;
use App\Ai\Tools\GetScheduleData;
use App\Ai\Tools\GetStudentDetails;
use App\Models\ChatbotSetting;
use App\Models\User;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasMiddleware;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

class AksaraAssistant implements Agent, Conversational, HasTools, HasMiddleware
{
    use RemembersConversations;
    use Promptable {
        prompt as traitPrompt;
    }

    protected Lab|string|array $activeProviders;

    /**
     * Get the agent's middleware.
     */
    public function middleware(): array
    {
        return [
            new LogAiTraffic,
        ];
    }

    public function __construct(public ?User $user = null)
    {
        $settings = ChatbotSetting::current();
        
        config([
            'ai.providers.openai.key' => $settings->openai_api_key,
            'ai.providers.openai.url' => $settings->openai_base_url,
            'ai.providers.gemini.key' => $settings->gemini_api_key,
            'ai.providers.groq.key' => $settings->groq_api_key,
        ]);

        $primary = $settings->provider ?: 'gemini';
        $fallbacks = $settings->getFallbackProvidersArray();
        $this->activeProviders = array_values(array_unique(array_filter(array_merge([$primary], $fallbacks))));
    }

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return 'Anda adalah Aksara AI, asisten virtual KHUSUS untuk Sistem Informasi Akademik Aksara. ' .
               'TUGAS ANDA: Memberikan data akademik secara cepat, akurat, dan rapi. ' .
               'FORMAT JAWABAN: ' .
               '1. Selalu gunakan format List atau Tabel Markdown untuk menampilkan data detail. ' .
               '2. Gunakan emoji yang relevan (misal: 👤 untuk siswa, 📚 untuk mapel, 🗓️ untuk jadwal) agar tampilan menarik. ' .
               '3. Hindari kalimat pembuka yang terlalu panjang. Langsung ke inti informasi yang diminta. ' .
               '4. Di akhir jawaban, berikan satu kalimat saran singkat untuk langkah selanjutnya. ' .
               'PROSEDUR WAJIB: ' .
               '- Jika tanya siswa/nilai/jadwal, WAJIB panggil Tool terkait. ' .
               '- Tampilkan data dari Tool secara lengkap. ' .
               '- TOLAK pertanyaan di luar Aksara System dengan sopan.';
    }

    /**
     * Override prompt method to inject our dynamic providers.
     */
    public function prompt(
        string $prompt, 
        array $attachments = [], 
        Lab|array|string|null $provider = null, 
        string|null $model = null, 
        int|null $timeout = null
    ): \Laravel\Ai\Responses\AgentResponse {
        $provider = $provider ?: $this->activeProviders;

        return $this->traitPrompt($prompt, $attachments, $provider, $model, $timeout);
    }

    /**
     * Get the tools available to the agent.
     *
     * @return iterable
     */
    public function tools(): iterable
    {
        return [
            new GetStudentDetails($this->user),
            new GetAcademicData($this->user),
            new GetScheduleData($this->user),
            new GetReportLink($this->user),
        ];
    }
}
