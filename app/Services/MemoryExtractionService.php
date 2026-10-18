<?php

namespace App\Services;

use App\Models\Character;
use App\Models\Chat;
use RuntimeException;

class MemoryExtractionService
{
    public function __construct(
        protected VeniceChatService $venice,
        protected AppSettingsService $settings,
    ) {
    }

    public function extract(Chat $chat, Character $character): array
    {
        $messages = $chat->messages()
            ->latest('id')
            ->take(8)
            ->get()
            ->reverse()
            ->map(fn ($message) => strtoupper($message->role).': '.$message->content)
            ->implode("\n\n");

        if ($messages === '') {
            return [];
        }

        $payload = [
            'model' => 'venice-uncensored',
            'temperature' => 0.3,
            'max_tokens' => 350,
            'top_p' => 0.9,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => implode("\n", [
                        'Extract stable memory candidates from this private character chat.',
                        'Return valid JSON only.',
                        'Schema: {"entries":[{"title":"...","content":"...","category":"relationship|preference|goal|lore|constraint|plan|emotion","scope":"chat|character","is_pinned":false}]}',
                        'Only include durable facts worth remembering later.',
                        'Skip temporary filler or redundant points.',
                        'Keep to at most 4 entries.',
                    ]),
                ],
                [
                    'role' => 'user',
                    'content' => implode("\n\n", [
                        'Character: '.$character->name,
                        'Scenario: '.$character->scenario,
                        'Recent conversation:',
                        $messages,
                    ]),
                ],
            ],
        ];

        $response = $this->venice->rawCompletion($payload);
        $content = trim((string) data_get($response, 'choices.0.message.content', ''));

        if ($content === '') {
            return [];
        }

        $decoded = json_decode($content, true);

        if (! is_array($decoded) || ! isset($decoded['entries']) || ! is_array($decoded['entries'])) {
            throw new RuntimeException('Memory extraction returned invalid JSON.');
        }

        return collect($decoded['entries'])
            ->map(function ($entry) {
                return [
                    'title' => trim((string) data_get($entry, 'title', 'Memory')),
                    'content' => trim((string) data_get($entry, 'content', '')),
                    'category' => trim((string) data_get($entry, 'category', 'general')),
                    'scope' => data_get($entry, 'scope', 'chat') === 'character' ? 'character' : 'chat',
                    'is_pinned' => (bool) data_get($entry, 'is_pinned', false),
                ];
            })
            ->filter(fn ($entry) => $entry['title'] !== '' && $entry['content'] !== '')
            ->take((int) $this->settings->get('app', 'memory_extraction_limit', 3))
            ->values()
            ->all();
    }
}
