<?php

namespace App\Ai\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Ai\Responses\AgentResponse;

class LogAiTraffic
{
    /**
     * Handle the incoming prompt.
     */
    public function handle(AgentPrompt $prompt, Closure $next)
    {
        Log::info('--- AI PROMPT START ---');
        Log::info('User Prompt: ' . $prompt->prompt);
        
        $response = $next($prompt);

        if ($response instanceof AgentResponse) {
            Log::info('AI Response: ' . $response->text);
            if (!empty($response->usage)) {
                // Ensure usage is converted to array for logging
                Log::info('Token Usage: ', (array) $response->usage);
            }
        }

        Log::info('--- AI PROMPT END ---');

        return $response;
    }
}
