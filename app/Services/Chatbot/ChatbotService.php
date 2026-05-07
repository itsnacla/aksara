<?php

namespace App\Services\Chatbot;

use App\Models\ChatbotSetting;
use App\Services\Chatbot\Contracts\AIProviderInterface;
use App\Services\Chatbot\Providers\GeminiProvider;
use App\Services\Chatbot\Providers\OpenAIProvider;
use App\Services\Chatbot\Providers\GroqProvider;
use Illuminate\Support\Facades\Log;

/**
 * Main Chatbot Service.
 * Reads provider config from the database (managed by super admin via Filament).
 * Supports automatic fallback to next provider on failure.
 */
class ChatbotService
{
    private array $providers = [];
    private ChatbotSetting $settings;

    public function __construct()
    {
        $this->settings = ChatbotSetting::current();
        $this->providers = $this->resolveProviders();
    }

    /**
     * Check if the chatbot is active.
     */
    public function isActive(): bool
    {
        return $this->settings->is_active;
    }

    /**
     * Get the primary AI provider instance.
     */
    public function getProvider(): ?AIProviderInterface
    {
        return $this->providers[0] ?? null;
    }

    /**
     * Send a chat request with automatic provider fallback.
     */
    public function chat(
        string $systemInstruction,
        array $contents,
        array $tools,
        callable $functionExecutor
    ): string {
        foreach ($this->providers as $provider) {
            try {
                Log::debug("Chatbot: trying provider {$provider->getName()}");
                return $provider->chat($systemInstruction, $contents, $tools, $functionExecutor);
            } catch (\Exception $e) {
                Log::warning("Chatbot: provider {$provider->getName()} failed", [
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
        }

        throw new \RuntimeException('All AI providers failed.');
    }

    /**
     * Resolve provider instances based on DB settings.
     */
    private function resolveProviders(): array
    {
        $primary = $this->settings->provider;
        $fallbacks = $this->settings->getFallbackProvidersArray();

        $order = array_unique(array_merge([$primary], $fallbacks));
        $providers = [];

        foreach ($order as $name) {
            $provider = $this->makeProvider($name);
            if ($provider) {
                $providers[] = $provider;
            }
        }

        return $providers;
    }

    /**
     * Create provider instance by name, using DB settings.
     */
    private function makeProvider(string $name): ?AIProviderInterface
    {
        $apiKey = $this->settings->getApiKeyFor($name);

        if (empty($apiKey)) {
            return null;
        }

        $model = $this->settings->getModelFor($name);

        return match ($name) {
            'gemini' => new GeminiProvider($apiKey, $model),
            'openai' => new OpenAIProvider($apiKey, $model, $this->settings->openai_base_url),
            'groq' => new GroqProvider($apiKey, $model),
            default => null,
        };
    }
}
