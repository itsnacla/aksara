<?php

namespace App\Services\Chatbot\Providers;

use App\Services\Chatbot\Contracts\AIProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * OpenAI-compatible AI Provider.
 * Works with OpenAI (GPT-4o, GPT-4o-mini) and any OpenAI-compatible API.
 */
class OpenAIProvider implements AIProviderInterface
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl;

    public function __construct(string $apiKey, string $model = 'gpt-4o-mini', string $baseUrl = 'https://api.openai.com/v1')
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
        $this->baseUrl = $baseUrl;
    }

    public function getName(): string
    {
        return 'OpenAI';
    }

    public function chat(
        string $systemInstruction,
        array $contents,
        array $tools,
        callable $functionExecutor
    ): string {
        $messages = $this->formatMessages($systemInstruction, $contents);
        $openaiTools = $this->formatTools($tools);

        for ($i = 0; $i < 3; $i++) {
            $payload = [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 2048,
            ];

            if (!empty($openaiTools)) {
                $payload['tools'] = $openaiTools;
                $payload['tool_choice'] = 'auto';
            }

            $response = Http::timeout(40)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->baseUrl}/chat/completions", $payload);

            if (!$response->successful()) {
                Log::warning('OpenAI API error', ['status' => $response->status(), 'body' => $response->body()]);
                if ($response->status() === 429) return 'Maaf, AI sedang sibuk. Coba lagi nanti ya! ⏳';
                throw new \RuntimeException('OpenAI API failed: ' . $response->status());
            }

            $data = $response->json();
            $message = $data['choices'][0]['message'] ?? null;

            if (!empty($message['tool_calls'])) {
                $messages[] = $message;

                foreach ($message['tool_calls'] as $toolCall) {
                    $result = $functionExecutor(
                        $toolCall['function']['name'],
                        json_decode($toolCall['function']['arguments'], true) ?? []
                    );

                    $messages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $toolCall['id'],
                        'content' => json_encode($result, JSON_UNESCAPED_UNICODE),
                    ];
                }
                continue;
            }

            return $message['content'] ?? 'Maaf, saya tidak bisa memproses permintaan Anda.';
        }

        return 'Maaf, proses analisis data memakan waktu terlalu lama.';
    }

    protected function formatMessages(string $systemInstruction, array $contents): array
    {
        $messages = [['role' => 'system', 'content' => $systemInstruction]];

        foreach ($contents as $msg) {
            $messages[] = [
                'role' => $msg['role'] === 'user' ? 'user' : 'assistant',
                'content' => $msg['content'],
            ];
        }

        return $messages;
    }

    protected function formatTools(array $tools): array
    {
        return array_map(fn($tool) => [
            'type' => 'function',
            'function' => [
                'name' => $tool['name'],
                'description' => $tool['description'],
                'parameters' => $tool['parameters'],
            ],
        ], $tools);
    }
}
