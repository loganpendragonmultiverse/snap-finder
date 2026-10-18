<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\ChatTurnLog;
use App\Services\VeniceChatService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComparisonController extends Controller
{
    public function index(Request $request, VeniceChatService $venice): View
    {
        return view('compare.index', [
            'characters' => Character::query()->where('user_id', $request->user()->id)->latest('updated_at')->get(),
            'modelOptions' => $venice->availableModels(),
            'results' => [],
            'selectedModels' => [],
        ]);
    }

    public function store(Request $request, VeniceChatService $venice): View
    {
        $data = $request->validate([
            'character_id' => ['nullable', 'integer', 'exists:characters,id'],
            'prompt' => ['required', 'string'],
            'models' => ['required', 'array', 'min:1'],
            'models.*' => ['required', 'string'],
        ]);

        $character = $data['character_id']
            ? Character::query()->where('user_id', $request->user()->id)->findOrFail($data['character_id'])
            : null;

        $results = [];

        foreach ($data['models'] as $modelId) {
            if (($venice->availableModels()[$modelId]['status'] ?? 'planned') !== 'active') {
                continue;
            }

            $payload = [
                'model' => $modelId,
                'temperature' => 0.9,
                'max_tokens' => 900,
                'top_p' => 0.95,
                'messages' => array_values(array_filter([
                    $character ? [
                        'role' => 'system',
                        'content' => implode("\n\n", array_filter([
                            'You are being evaluated inside Pendragon Chat Bot.',
                            'Stay aligned with the following character profile.',
                            'Character name: '.$character->name,
                            'Short description: '.$character->short_description,
                            'Personality: '.$character->personality,
                            'Scenario: '.$character->scenario,
                            $character->writing_style ? 'Writing style: '.$character->writing_style : null,
                            $character->tone ? 'Tone: '.$character->tone : null,
                            $character->lore_notes ? 'Lore notes: '.$character->lore_notes : null,
                        ])),
                    ] : null,
                    [
                        'role' => 'user',
                        'content' => $data['prompt'],
                    ],
                ])),
            ];

            try {
                $response = $venice->rawCompletion($payload);
                $results[$modelId] = [
                    'label' => $venice->availableModels()[$modelId]['label'] ?? $modelId,
                    'content' => trim((string) data_get($response, 'choices.0.message.content', '')),
                    'usage' => data_get($response, 'usage', []),
                ];
            } catch (\Throwable $exception) {
                $results[$modelId] = [
                    'label' => $venice->availableModels()[$modelId]['label'] ?? $modelId,
                    'content' => 'Request failed: '.$exception->getMessage(),
                    'usage' => [],
                ];
            }

            ChatTurnLog::query()->create([
                'chat_id' => null,
                'message_id' => null,
                'event_type' => 'comparison_'.$modelId,
                'request_payload' => $payload,
                'response_payload' => $results[$modelId],
                'status_code' => 200,
            ]);
        }

        return view('compare.index', [
            'characters' => Character::query()->where('user_id', $request->user()->id)->latest('updated_at')->get(),
            'modelOptions' => $venice->availableModels(),
            'results' => $results,
            'selectedModels' => $data['models'],
            'selectedCharacterId' => $data['character_id'] ?? null,
            'prompt' => $data['prompt'],
        ]);
    }
}
