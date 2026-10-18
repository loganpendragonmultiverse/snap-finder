<?php

namespace App\Services;

use App\Models\Character;
use App\Models\Chat;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class VeniceChatService
{
    public function __construct(protected AppSettingsService $settings)
    {
    }

    public function availableModels(): array
    {
        return config('pendragon.models', []);
    }

    public function sendReply(Chat $chat, Character $character): array
    {
        $payload = [
            'model' => $chat->model,
            'temperature' => $chat->temperature,
            'max_tokens' => $chat->max_tokens,
            'top_p' => $chat->top_p,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->buildSystemPrompt($chat, $character),
                ],
            ],
        ];

        foreach ($chat->messages()->orderBy('id')->get(['role', 'content']) as $message) {
            $payload['messages'][] = [
                'role' => $message->role,
                'content' => $message->content,
            ];
        }

        $response = $this->client()->post('/chat/completions', $payload);

        return $this->parseResponse($response, $payload);
    }

    public function rawCompletion(array $payload): array
    {
        $response = $this->client()->post('/chat/completions', $payload);
        $response->throw();

        return $response->json();
    }

    public function generateSummary(Chat $chat): string
    {
        $messages = $chat->messages()
            ->latest('id')
            ->take((int) $this->settings->get('app', 'summary_message_limit', config('pendragon.summary_message_limit', 12)))
            ->get()
            ->reverse()
            ->map(fn ($message) => strtoupper($message->role).': '.$message->content)
            ->implode("\n\n");

        if ($messages === '') {
            return '';
        }

        try {
            $response = $this->client()->post('/chat/completions', [
                'model' => 'venice-uncensored',
                'temperature' => 0.4,
                'max_tokens' => 220,
                'top_p' => 0.9,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Summarize the important relationship, emotional state, lore, and unresolved threads from this private character chat in 5 concise sentences without markdown.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $messages,
                    ],
                ],
            ]);

            $content = data_get($response->json(), 'choices.0.message.content');

            if (is_string($content) && trim($content) !== '') {
                return trim($content);
            }
        } catch (\Throwable) {
            // Fall back when the summary request is unavailable.
        }

        return mb_substr(str_replace("\n", ' ', $messages), 0, 700);
    }

    public function promptPreview(Chat $chat, Character $character): string
    {
        return $this->buildSystemPrompt($chat, $character);
    }

    protected function client()
    {
        $key = $this->settings->get('app', 'venice_admin_key', config('services.venice.admin_key'));

        if (! $key) {
            throw new RuntimeException('VENICE_ADMIN_KEY is not configured.');
        }

        return Http::baseUrl(config('services.venice.base_url'))
            ->withToken($key)
            ->acceptJson()
            ->timeout(90);
    }

    protected function parseResponse(Response $response, array $payload): array
    {
        $statusCode = $response->status();

        $response->throw();

        return [
            'content' => trim((string) data_get($response->json(), 'choices.0.message.content', '')),
            'meta' => [
                'id' => data_get($response->json(), 'id'),
                'usage' => data_get($response->json(), 'usage', []),
                'model' => data_get($response->json(), 'model'),
                'status_code' => $statusCode,
            ],
            'request_payload' => $payload,
            'response_payload' => $response->json(),
        ];
    }

    protected function buildSystemPrompt(Chat $chat, Character $character): string
    {
        $summary = trim((string) optional($chat->summary)->summary);
        $notes = trim((string) optional($chat->summary)->manual_notes);
        $memory = $chat->memoryEntries()->limit(8)->get()
            ->map(fn ($entry) => '['.$entry->category.'] '.$entry->title.': '.$entry->content)
            ->implode("\n");
        $characterMemory = $character->memoryEntries()->limit(8)->get()
            ->map(fn ($entry) => '['.$entry->category.'] '.$entry->title.': '.$entry->content)
            ->implode("\n");

        return trim(implode("\n\n", array_filter([
            'You are participating in a private character chat system called Pendragon Chat Bot.',
            'Stay in character, preserve continuity, and respond with natural dialogue.',
            'Character name: '.$character->name,
            'Short description: '.$character->short_description,
            'Personality: '.$character->personality,
            'Scenario: '.$character->scenario,
            $character->writing_style ? 'Writing style: '.$character->writing_style : null,
            $character->tone ? 'Tone: '.$character->tone : null,
            $character->long_bio ? 'Long bio: '.$character->long_bio : null,
            $character->lore_notes ? 'Lore notes: '.$character->lore_notes : null,
            $chat->pinned_memory ? 'Pinned memory: '.$chat->pinned_memory : null,
            $summary ? 'Rolling summary: '.$summary : null,
            $notes ? 'Manual memory notes: '.$notes : null,
            $memory ? 'Chat memory facts:'."\n".$memory : null,
            $characterMemory ? 'Character memory facts:'."\n".$characterMemory : null,
            'Avoid generic assistant framing unless the user explicitly requests out-of-character analysis.',
        ])));
    }
}
