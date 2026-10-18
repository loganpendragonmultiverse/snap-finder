<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\ChatTurnLog;
use App\Models\Message;
use App\Services\MemoryExtractionService;
use App\Services\VeniceChatService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class MessageController extends Controller
{
    public function store(Request $request, Chat $chat, VeniceChatService $venice, MemoryExtractionService $memoryExtraction): RedirectResponse
    {
        abort_unless($chat->user_id === $request->user()->id, 404);

        $data = $request->validate([
            'content' => ['required', 'string'],
        ]);

        if (config('pendragon.models.'.$chat->model.'.status') !== 'active') {
            return back()->withErrors([
                'content' => 'That model slot is reserved for a future release and is not live yet.',
            ]);
        }

        $chat->loadMissing(['character', 'summary']);

        $userMessage = Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => $data['content'],
            'model' => $chat->model,
        ]);

        return $this->generateAssistantReply($chat, $venice, $memoryExtraction, 'message_send', $userMessage->id);
    }

    public function regenerate(Request $request, Chat $chat, VeniceChatService $venice, MemoryExtractionService $memoryExtraction): RedirectResponse
    {
        abort_unless($chat->user_id === $request->user()->id, 404);

        $lastAssistant = $chat->messages()->latest('id')->first();

        if (! $lastAssistant || $lastAssistant->role !== 'assistant') {
            return back()->withErrors([
                'content' => 'There is no assistant reply available to regenerate yet.',
            ]);
        }

        $lastAssistant->delete();

        return $this->generateAssistantReply($chat->fresh(), $venice, $memoryExtraction, 'regenerate_reply');
    }

    public function update(Request $request, Message $message): RedirectResponse
    {
        abort_unless($message->chat->user_id === $request->user()->id, 404);

        $message->update([
            'content' => $request->validate([
                'content' => ['required', 'string'],
            ])['content'],
            'edited_at' => now(),
        ]);

        return back()->with('status', 'Message updated. Branch or regenerate if you want a new downstream response.');
    }

    public function destroy(Request $request, Message $message): RedirectResponse
    {
        abort_unless($message->chat->user_id === $request->user()->id, 404);

        $message->delete();

        return back()->with('status', 'Message deleted.');
    }

    protected function generateAssistantReply(Chat $chat, VeniceChatService $venice, MemoryExtractionService $memoryExtraction, string $eventType, ?int $userMessageId = null): RedirectResponse
    {
        try {
            $reply = $venice->sendReply($chat->fresh(), $chat->character);

            $assistantMessage = Message::create([
                'chat_id' => $chat->id,
                'role' => 'assistant',
                'content' => $reply['content'],
                'model' => $chat->model,
                'meta' => $reply['meta'],
                'token_count' => (int) data_get($reply, 'meta.usage.total_tokens', 0),
            ]);

            ChatTurnLog::create([
                'chat_id' => $chat->id,
                'message_id' => $userMessageId ?? $assistantMessage->id,
                'event_type' => $eventType,
                'request_payload' => $reply['request_payload'],
                'response_payload' => $reply['response_payload'],
                'status_code' => (int) data_get($reply, 'meta.status_code'),
            ]);

            $chat->summary()->updateOrCreate(
                ['chat_id' => $chat->id],
                [
                    'summary' => $venice->generateSummary($chat->fresh()),
                    'last_generated_at' => now(),
                ]
            );

            $this->extractMemory($chat->fresh(['character']), $memoryExtraction);

            return redirect()->route('chats.show', $chat)->with('status', 'Reply generated.');
        } catch (Throwable $exception) {
            ChatTurnLog::create([
                'chat_id' => $chat->id,
                'message_id' => $userMessageId,
                'event_type' => $eventType.'_failed',
                'status_code' => 500,
                'error_message' => $exception->getMessage(),
            ]);

            return back()->withErrors([
                'content' => 'Venice request failed: '.$exception->getMessage(),
            ]);
        }
    }

    protected function extractMemory(Chat $chat, MemoryExtractionService $memoryExtraction): void
    {
        try {
            foreach ($memoryExtraction->extract($chat, $chat->character) as $entry) {
                $target = $entry['scope'] === 'character'
                    ? $chat->character->memoryEntries()
                    : $chat->memoryEntries();

                $exists = $target->where('title', $entry['title'])
                    ->where('content', $entry['content'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                $target->create([
                    'user_id' => $chat->user_id,
                    'chat_id' => $entry['scope'] === 'character' ? null : $chat->id,
                    'character_id' => $chat->character_id,
                    'title' => $entry['title'],
                    'content' => $entry['content'],
                    'category' => $entry['category'],
                    'source' => 'auto_extract',
                    'is_pinned' => $entry['is_pinned'],
                ]);
            }
        } catch (Throwable $exception) {
            ChatTurnLog::create([
                'chat_id' => $chat->id,
                'message_id' => null,
                'event_type' => 'memory_extract_failed',
                'status_code' => 500,
                'error_message' => $exception->getMessage(),
            ]);
        }
    }
}
