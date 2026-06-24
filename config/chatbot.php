<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Primary AI Provider
    |--------------------------------------------------------------------------
    |
    | The default AI provider for the chatbot. Supported: "gemini", "openai", "groq"
    |
    */
    'provider' => env('CHATBOT_PROVIDER', 'gemini'),

    /*
    |--------------------------------------------------------------------------
    | Fallback Providers
    |--------------------------------------------------------------------------
    |
    | If the primary provider fails (API error, rate limit, etc.),
    | these providers will be tried in order. Only providers with
    | configured API keys will be used.
    |
    */
    'fallback_providers' => array_filter(explode(',', env('CHATBOT_FALLBACK_PROVIDERS', ''))),

    /*
    |--------------------------------------------------------------------------
    | Provider Configurations
    |--------------------------------------------------------------------------
    */
    'providers' => [

        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
        ],

        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        ],

        'groq' => [
            'api_key' => env('GROQ_API_KEY'),
            'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
        ],

    ],

];
