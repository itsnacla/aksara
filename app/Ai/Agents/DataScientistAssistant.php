<?php

namespace App\Ai\Agents;

use App\Models\ChatbotSetting;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Laravel\Ai\Responses\AgentResponse;

#[Provider([Lab::Gemini, Lab::OpenAI, Lab::Groq])]
#[Model('gemini-2.0-flash')]
class DataScientistAssistant implements Agent
{
    use Promptable {
        prompt as traitPrompt;
    }

    protected ?string $activeModel = null;

    protected ?Lab $activeProvider = null;

    public function __construct()
    {
        $settings = ChatbotSetting::current();
        $providerName = $settings->provider ?: 'gemini';
        $this->activeModel = $settings->getModelFor($providerName);

        $this->activeProvider = match ($providerName) {
            'openai' => Lab::OpenAI,
            'groq' => Lab::Groq,
            default => Lab::Gemini,
        };
    }

    public function instructions(): string
    {
        return 'Anda adalah Data Scientist sistem akademik. Anda harus merespons murni dalam format JSON. TANPA markdown block (```json), TANPA teks tambahan, TANPA sapaan. HANYA raw JSON text yang valid.';
    }

    public function prompt(
        string $prompt,
        array $attachments = [],
        Lab|array|string|null $provider = null,
        ?string $model = null,
        ?int $timeout = null
    ): AgentResponse {
        $model = $model ?: $this->activeModel;
        $provider = $provider ?: $this->activeProvider;
        $timeout = $timeout ?: 300; // 5 minutes timeout for large json

        return $this->traitPrompt($prompt, $attachments, $provider, $model, $timeout);
    }
}
