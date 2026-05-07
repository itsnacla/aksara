<?php

namespace App\Services\Chatbot\Contracts;

/**
 * Contract for AI chat providers.
 * Each provider must handle its own API format for messages, tools, and function calling.
 */
interface AIProviderInterface
{
    /**
     * Send a chat request to the AI provider and return the text response.
     * Must handle function calling loops internally.
     *
     * @param string $systemInstruction  The system prompt
     * @param array  $contents           Conversation history in a normalized format
     * @param array  $tools              Tool/function definitions in a normalized format
     * @param callable $functionExecutor Callback to execute a function: fn(string $name, array $args) => mixed
     * @return string The AI's final text response
     */
    public function chat(
        string $systemInstruction,
        array $contents,
        array $tools,
        callable $functionExecutor
    ): string;

    /**
     * Get the display name of this provider (for logging).
     */
    public function getName(): string;
}
