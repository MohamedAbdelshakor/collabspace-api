<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Laravel\Ai\Providers\Tools\ProviderTool;

final class CollabspaceAgent implements Agent, Conversational, HasTools
{
    use Promptable;

    /**
     * @param  list<Message>  $history
     */
    public function __construct(
        private readonly ?string $customInstructions = null,
        private readonly array $history = [],
    ) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): string
    {
        return $this->customInstructions
            ?? 'You are Collabspace AI (CB), an intelligent project management and team collaboration assistant. Answer concisely, accurately, and in the same language the user writes in (Arabic or English).';
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return list<Message>
     */
    public function messages(): iterable
    {
        return $this->history;
    }

    /**
     * Get the tools available to the agent.
     *
     * @return list<Agent|Tool|ProviderTool>
     */
    public function tools(): iterable
    {
        return [];
    }
}
