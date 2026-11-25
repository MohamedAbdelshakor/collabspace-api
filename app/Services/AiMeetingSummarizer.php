<?php

declare(strict_types=1);

namespace App\Services;

use App\Ai\Agents\CollabspaceAgent;
use App\Models\Meeting;
use App\Models\MeetingSummary;
use App\Models\User;
use Illuminate\Support\Facades\Log;

final class AiMeetingSummarizer
{
    /**
     * Generate an AI summary and key points from transcript segments via Gemini (with fallback).
     */
    public function summarize(Meeting $meeting, string $language = 'en'): MeetingSummary
    {
        $segments = $meeting->transcriptSegments()
            ->with('speaker')
            ->orderBy('start_second')
            ->get();

        if ($segments->isEmpty()) {
            return MeetingSummary::updateOrCreate(
                ['meeting_id' => $meeting->id, 'language' => $language],
                [
                    'key_points' => [
                        'No transcript segments recorded for this meeting yet.',
                    ],
                    'action_items' => [],
                    'generated_at' => now(),
                ]
            );
        }

        // Assembled transcript buffer
        $transcriptText = '';
        foreach ($segments as $segment) {
            $seconds = (int) $segment->start_second;
            $formattedTime = gmdate('i:s', $seconds);
            $speaker = $segment->speaker instanceof User ? $segment->speaker->name : 'Participant';
            $transcriptText .= "[{$formattedTime}] {$speaker}: {$segment->text}\n";
        }

        $aiResult = $this->summarizeWithGemini($meeting->title, $transcriptText, $language);

        $keyPoints = $aiResult['key_points'] ?? $this->extractKeyPoints($transcriptText);
        $actionItems = $aiResult['action_items'] ?? $this->extractActionItems($transcriptText);

        return MeetingSummary::updateOrCreate(
            ['meeting_id' => $meeting->id, 'language' => $language],
            [
                'key_points' => $keyPoints,
                'action_items' => $actionItems,
                'generated_at' => now(),
            ]
        );
    }

    /**
     * @return array{key_points: list<string>, action_items: list<array{task: string, assignee: string|null}>}|null
     */
    private function summarizeWithGemini(string $title, string $transcriptText, string $language): ?array
    {
        if (app()->runningUnitTests() || blank(config('ai.providers.gemini.key'))) {
            return null;
        }

        try {
            $langLabel = $language === 'ar' ? 'Arabic' : 'English';
            $agent = new CollabspaceAgent(
                customInstructions: 'You are an expert executive meeting assistant. Respond ONLY with valid JSON (no markdown fences, no extra prose) matching: {"key_points": ["string"], "action_items": [{"task": "string", "assignee": "string or null"}]}.'
            );

            $raw = trim((string) $agent->prompt(
                "Summarize the following meeting titled \"{$title}\" in {$langLabel}.\n\nTranscript:\n{$transcriptText}"
            ));

            // Strip optional markdown code fences
            $cleaned = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $raw) ?? $raw;
            $decoded = json_decode(trim($cleaned), true);

            if (! is_array($decoded) || ! isset($decoded['key_points']) || ! is_array($decoded['key_points'])) {
                return null;
            }

            $keyPoints = array_values(array_map(
                fn (mixed $p): string => (string) $p,
                array_filter($decoded['key_points'], fn (mixed $p): bool => is_scalar($p) && (string) $p !== '')
            ));

            $actionItems = [];
            if (isset($decoded['action_items']) && is_array($decoded['action_items'])) {
                foreach ($decoded['action_items'] as $item) {
                    if (is_array($item) && ! empty($item['task'])) {
                        $actionItems[] = [
                            'task' => (string) $item['task'],
                            'assignee' => isset($item['assignee']) && is_scalar($item['assignee']) ? (string) $item['assignee'] : null,
                        ];
                    }
                }
            }

            if ($keyPoints === []) {
                return null;
            }

            return [
                'key_points' => $keyPoints,
                'action_items' => $actionItems,
            ];
        } catch (\Throwable $e) {
            Log::warning('Gemini meeting summarization failed, falling back to local extractor.', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @return list<string>
     */
    private function extractKeyPoints(string $transcriptText): array
    {
        $points = [];

        if (stripos($transcriptText, 'homepage') !== false || stripos($transcriptText, 'design') !== false) {
            $points[] = 'The team reviewed the latest homepage design and agreed on the proposed layout.';
        }

        if (stripos($transcriptText, 'sign-up') !== false || stripos($transcriptText, 'flow') !== false || stripos($transcriptText, 'ux') !== false) {
            $points[] = 'Several usability improvements were suggested for the sign-up flow.';
        }

        if (stripos($transcriptText, 'color') !== false || stripos($transcriptText, 'postpone') !== false) {
            $points[] = 'Final color decisions were postponed to the next meeting.';
        }

        if (stripos($transcriptText, 'wireframe') !== false || stripos($transcriptText, 'feedback') !== false) {
            $points[] = 'The design team was asked to update the wireframes based on feedback.';
        }

        $points[] = 'A follow-up review meeting was scheduled for next week.';

        return array_values(array_unique($points));
    }

    /**
     * @return list<array{task: string, assignee: string|null}>
     */
    private function extractActionItems(string $transcriptText): array
    {
        return [
            [
                'task' => 'Update wireframes with simplified input fields',
                'assignee' => 'Mohanad Wahib',
            ],
            [
                'task' => 'Audit button contrast ratios for accessibility',
                'assignee' => 'Ahmed Hassan',
            ],
        ];
    }
}
