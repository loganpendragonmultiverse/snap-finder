<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\Chat;
use App\Models\ChatFolder;
use App\Models\ChatSummary;
use App\Models\Message;
use App\Services\AppSettingsService;
use App\Services\VeniceChatService;
use App\Services\VeniceImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(Request $request, VeniceChatService $venice, VeniceImageService $images): View
    {
        $query = trim((string) $request->input('q'));
        $activeChat = Chat::query()
            ->where('user_id', $request->user()->id)
            ->where('is_archived', false)
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($nested) use ($query) {
                    $nested->where('title', 'like', '%'.$query.'%')
                        ->orWhereHas('character', fn ($character) => $character->where('name', 'like', '%'.$query.'%'))
                        ->orWhereHas('messages', fn ($message) => $message->where('content', 'like', '%'.$query.'%'))
                        ->orWhereHas('memoryEntries', function ($memory) use ($query) {
                            $memory->where('title', 'like', '%'.$query.'%')
                                ->orWhere('content', 'like', '%'.$query.'%');
                        });
                });
            })
            ->with(['character', 'messages', 'summary', 'logs', 'memoryEntries', 'folder'])
            ->orderByDesc('is_pinned')
            ->latest('updated_at')
            ->first();

        return view('chats.index', [
            'characters' => Character::query()->where('user_id', $request->user()->id)->orderByDesc('is_favorite')->latest('updated_at')->get(),
            'chatFolders' => ChatFolder::query()->where('user_id', $request->user()->id)->withCount(['chats' => fn ($builder) => $builder->where('is_archived', false)])->orderByDesc('is_favorite')->latest('updated_at')->get(),
            'chats' => Chat::query()
                ->where('user_id', $request->user()->id)
                ->where('is_archived', false)
                ->with(['character', 'folder'])
                ->when($query !== '', function ($builder) use ($query) {
                    $builder->where(function ($nested) use ($query) {
                        $nested->where('title', 'like', '%'.$query.'%')
                            ->orWhereHas('character', fn ($character) => $character->where('name', 'like', '%'.$query.'%'))
                            ->orWhereHas('messages', fn ($message) => $message->where('content', 'like', '%'.$query.'%'))
                            ->orWhereHas('memoryEntries', function ($memory) use ($query) {
                                $memory->where('title', 'like', '%'.$query.'%')
                                    ->orWhere('content', 'like', '%'.$query.'%');
                            });
                    });
                })
                ->orderByDesc('is_pinned')
                ->latest('updated_at')
                ->get(),
            'archivedChats' => Chat::query()
                ->where('user_id', $request->user()->id)
                ->where('is_archived', true)
                ->with(['character', 'folder'])
                ->latest('archived_at')
                ->get(),
            'activeChat' => $activeChat,
            'modelOptions' => $venice->availableModels(),
            'imageModelOptions' => $images->availableModels(),
            'defaultImageModel' => app(AppSettingsService::class)->defaults()['default_image_model'],
            'promptPreview' => $activeChat ? $venice->promptPreview($activeChat, $activeChat->character) : null,
            'query' => $query,
        ]);
    }

    public function store(Request $request, Character $character, AppSettingsService $settings): RedirectResponse
    {
        abort_unless($character->user_id === $request->user()->id, 404);
        $folderId = $request->input('chat_folder_id');

        if ($folderId) {
            abort_unless(ChatFolder::query()->where('user_id', $request->user()->id)->whereKey($folderId)->exists(), 404);
        }

        $defaults = $settings->defaults();

        $chat = Chat::create([
            'user_id' => $request->user()->id,
            'character_id' => $character->id,
            'title' => $request->input('title', $character->name.' Session'),
            'model' => $request->input('model', $character->default_model ?: $defaults['default_model']),
            'temperature' => $request->input('temperature', $character->temperature ?: $defaults['default_temperature']),
            'max_tokens' => $request->input('max_tokens', $character->max_tokens ?: $defaults['default_max_tokens']),
            'top_p' => $request->input('top_p', $character->top_p ?: $defaults['default_top_p']),
            'pinned_memory' => $request->input('pinned_memory'),
            'is_pinned' => false,
            'chat_folder_id' => $folderId,
            'is_archived' => false,
        ]);

        ChatSummary::create([
            'chat_id' => $chat->id,
            'summary' => '',
            'manual_notes' => '',
        ]);

        Message::create([
            'chat_id' => $chat->id,
            'role' => 'assistant',
            'content' => $character->first_message,
            'model' => $chat->model,
        ]);

        return redirect()->route('chats.show', $chat);
    }

    public function show(Request $request, Chat $chat, VeniceChatService $venice, VeniceImageService $images): View
    {
        abort_unless($chat->user_id === $request->user()->id, 404);

        $chat->load(['character', 'messages', 'summary', 'logs', 'memoryEntries', 'folder']);

        return view('chats.index', [
            'characters' => Character::query()->where('user_id', $request->user()->id)->orderByDesc('is_favorite')->latest('updated_at')->get(),
            'chatFolders' => ChatFolder::query()->where('user_id', $request->user()->id)->withCount(['chats' => fn ($builder) => $builder->where('is_archived', false)])->orderByDesc('is_favorite')->latest('updated_at')->get(),
            'chats' => Chat::query()->where('user_id', $request->user()->id)->where('is_archived', false)->with(['character', 'folder'])->orderByDesc('is_pinned')->latest('updated_at')->get(),
            'archivedChats' => Chat::query()->where('user_id', $request->user()->id)->where('is_archived', true)->with(['character', 'folder'])->latest('archived_at')->get(),
            'activeChat' => $chat,
            'modelOptions' => $venice->availableModels(),
            'imageModelOptions' => $images->availableModels(),
            'defaultImageModel' => app(AppSettingsService::class)->defaults()['default_image_model'],
            'promptPreview' => $venice->promptPreview($chat, $chat->character),
            'query' => '',
        ]);
    }

    public function branch(Request $request, Chat $chat): RedirectResponse
    {
        abort_unless($chat->user_id === $request->user()->id, 404);

        $chat->loadMissing(['messages', 'summary', 'memoryEntries']);

        $branched = Chat::create([
            'user_id' => $chat->user_id,
            'character_id' => $chat->character_id,
            'title' => $chat->title.' Branch',
            'model' => $chat->model,
            'temperature' => $chat->temperature,
            'max_tokens' => $chat->max_tokens,
            'top_p' => $chat->top_p,
            'pinned_memory' => $chat->pinned_memory,
            'is_pinned' => false,
            'chat_folder_id' => $chat->chat_folder_id,
            'is_archived' => false,
        ]);

        foreach ($chat->messages as $message) {
            $branched->messages()->create([
                'role' => $message->role,
                'content' => $message->content,
                'model' => $message->model,
                'token_count' => $message->token_count,
                'meta' => $message->meta,
                'edited_at' => $message->edited_at,
            ]);
        }

        foreach ($chat->memoryEntries as $entry) {
            $branched->memoryEntries()->create([
                'user_id' => $request->user()->id,
                'character_id' => $chat->character_id,
                'title' => $entry->title,
                'content' => $entry->content,
                'category' => $entry->category,
                'source' => $entry->source,
                'is_pinned' => $entry->is_pinned,
            ]);
        }

        $branched->summary()->create([
            'summary' => optional($chat->summary)->summary,
            'manual_notes' => optional($chat->summary)->manual_notes,
            'last_generated_at' => optional($chat->summary)->last_generated_at,
        ]);

        return redirect()->route('chats.show', $branched)->with('status', 'Branch created.');
    }

    public function update(Request $request, Chat $chat): RedirectResponse
    {
        abort_unless($chat->user_id === $request->user()->id, 404);

        $folderId = $request->input('chat_folder_id');

        if ($folderId) {
            abort_unless(ChatFolder::query()->where('user_id', $request->user()->id)->whereKey($folderId)->exists(), 404);
        }

        $chat->update($request->validate([
            'title' => ['required', 'string', 'max:120'],
            'model' => ['required', 'string', 'max:120'],
            'temperature' => ['required', 'numeric', 'between:0,2'],
            'max_tokens' => ['required', 'integer', 'min:128', 'max:4096'],
            'top_p' => ['required', 'numeric', 'between:0,1'],
            'pinned_memory' => ['nullable', 'string'],
            'is_pinned' => ['nullable', 'boolean'],
            'chat_folder_id' => ['nullable', 'integer'],
        ]) + [
            'is_pinned' => $request->boolean('is_pinned'),
            'chat_folder_id' => $folderId,
        ]);

        return back()->with('status', 'Chat settings updated.');
    }

    public function destroy(Request $request, Chat $chat): RedirectResponse
    {
        abort_unless($chat->user_id === $request->user()->id, 404);

        $chat->delete();

        return redirect()->route('chats.index')->with('status', 'Chat deleted.');
    }
}
