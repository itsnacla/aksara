<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider([Lab::Gemini, Lab::OpenAI, Lab::Groq])] // Pure Failover Support
#[Model('gemini-2.0-flash')] // Default Model
#[Temperature(0.7)]
#[MaxSteps(5)]
#[Timeout(120)]
class WaliKelasAgent implements Agent
{
    use Promptable;

    protected string $systemInstruction;

    /**
     * Create a new agent instance.
     */
    public function __construct(string $systemInstruction)
    {
        $this->systemInstruction = $systemInstruction;
    }

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return $this->systemInstruction;
    }
}
