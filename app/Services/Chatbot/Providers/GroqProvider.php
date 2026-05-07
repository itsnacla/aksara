<?php

namespace App\Services\Chatbot\Providers;

/**
 * Groq AI Provider.
 * Uses OpenAI-compatible API, so we extend OpenAIProvider.
 */
class GroqProvider extends OpenAIProvider
{
    public function __construct(string $apiKey, string $model = 'llama-3.3-70b-versatile')
    {
        parent::__construct($apiKey, $model, 'https://api.groq.com/openai/v1');
    }

    public function getName(): string
    {
        return 'Groq';
    }
}
