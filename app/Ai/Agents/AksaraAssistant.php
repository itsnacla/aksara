<?php

namespace App\Ai\Agents;

use App\Ai\Middleware\LogAiTraffic;
use App\Ai\Tools\GetAcademicData;
use App\Ai\Tools\GetReportLink;
use App\Ai\Tools\GetScheduleData;
use App\Ai\Tools\GetStudentDetails;
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
use Stringable;

#[Provider([Lab::Gemini, Lab::OpenAI, Lab::Groq])] // Pure Failover Support
#[Model('gemini-2.0-flash')] // Default Model
#[Temperature(0.7)]
#[MaxSteps(10)]
#[Timeout(120)]
class AksaraAssistant implements Agent, Conversational, HasTools, HasMiddleware
{
    use RemembersConversations;
    use Promptable {
        prompt as traitPrompt;
    }

    protected ?string $activeModel = null;

    /**
     * Create a new agent instance.
     */
    public function __construct(public ?User $user = null)
    {
        $settings = \App\Models\ChatbotSetting::current();
        $this->activeModel = $settings->getModelFor($settings->provider ?: 'gemini');
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
               '2. Gunakan emoji yang relevan (misal: 👤 siswa, 🗓️ jadwal) agar tampilan menarik. ' .
               '3. Langsung ke inti informasi yang diminta, hindari basa-basi panjang. ' .
               '4. Di akhir jawaban, berikan satu kalimat saran singkat. ' .
               'PROSEDUR WAJIB: ' .
               '- Jika tanya siswa/nilai/jadwal, WAJIB panggil Tool terkait. ' .
               '- Tampilkan data dari Tool secara lengkap. ' .
               '- TOLAK pertanyaan di luar sistem akademik Aksara dengan sopan.';
    }

    /**
     * Get the tools available to the agent.
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
     * Override prompt to use dynamic model from DB.
     */
    public function prompt(
        string $prompt, 
        array $attachments = [], 
        Lab|array|string|null $provider = null, 
        string|null $model = null, 
        int|null $timeout = null
    ): \Laravel\Ai\Responses\AgentResponse {
        $model = $model ?: $this->activeModel;

        return $this->traitPrompt($prompt, $attachments, $provider, $model, $timeout);
    }
}
