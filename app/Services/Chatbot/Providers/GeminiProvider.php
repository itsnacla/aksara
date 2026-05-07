<?php

namespace App\Services\Chatbot\Providers;

use App\Services\Chatbot\Contracts\AIProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Google Gemini AI Provider.
 */
class GeminiProvider implements AIProviderInterface
{
    private string $apiKey;
    private string $model;

    public function __construct(string $apiKey, string $model = 'gemini-2.0-flash')
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
    }

    public function getName(): string
    {
        return 'Gemini';
    }

    public function chat(
        string $systemInstruction,
        array $contents,
        array $tools,
        callable $functionExecutor
    ): string {
        $geminiContents = $this->formatContents($contents);
        $geminiTools = $this->formatTools($tools);

        for ($i = 0; $i < 3; $i++) {
            $response = Http::timeout(40)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}",
                [
                    'system_instruction' => [
                        'parts' => [['text' => $systemInstruction]],
                    ],
                    'contents' => $geminiContents,
                    'tools' => $geminiTools,
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 2048,
                    ],
                ]
            );

            if (!$response->successful()) {
                Log::warning('Gemini API error', ['status' => $response->status(), 'body' => $response->body()]);
                if ($response->status() === 429) return 'Maaf, AI sedang sibuk. Coba lagi nanti ya! ⏳';
                throw new \RuntimeException('Gemini API failed: ' . $response->status());
            }

            $data = $response->json();
            $part = $data['candidates'][0]['content']['parts'][0] ?? null;

            if (isset($part['functionCall'])) {
                $fc = $part['functionCall'];
                $result = $functionExecutor($fc['name'], $fc['args'] ?? []);

                $geminiContents[] = ['role' => 'model', 'parts' => [['functionCall' => $fc]]];
                $geminiContents[] = ['role' => 'user', 'parts' => [[
                    'functionResponse' => [
                        'name' => $fc['name'],
                        'response' => ['name' => $fc['name'], 'content' => $result],
                    ],
                ]]];
                continue;
            }

            return $part['text'] ?? 'Maaf, saya tidak bisa memproses permintaan Anda.';
        }

        return 'Maaf, proses analisis data memakan waktu terlalu lama.';
    }

    private function formatContents(array $contents): array
    {
        return array_map(fn($msg) => [
            'role' => $msg['role'] === 'user' ? 'user' : 'model',
            'parts' => [['text' => $msg['content']]],
        ], $contents);
    }

    private function formatTools(array $tools): array
    {
        $declarations = array_map(fn($tool) => [
            'name' => $tool['name'],
            'description' => $tool['description'],
            'parameters' => $tool['parameters'],
        ], $tools);

        return [['function_declarations' => $declarations]];
    }
}
