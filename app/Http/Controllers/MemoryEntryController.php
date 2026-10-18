<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\Chat;
use App\Models\MemoryEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MemoryEntryController extends Controller
{
    public function storeForChat(Request $request, Chat $chat): RedirectResponse
    {
        abort_unless($chat->user_id === $request->user()->id, 404);

        $chat->memoryEntries()->create($this->validated($request) + [
            'user_id' => $request->user()->id,
            'character_id' => $chat->character_id,
            'source' => 'manual',
        ]);

        return back()->with('status', 'Chat memory saved.');
    }

    public function storeForCharacter(Request $request, Character $character): RedirectResponse
    {
        abort_unless($character->user_id === $request->user()->id, 404);

        $character->memoryEntries()->create($this->validated($request) + [
            'user_id' => $request->user()->id,
            'source' => 'manual',
        ]);

        return back()->with('status', 'Character memory saved.');
    }

    public function destroy(Request $request, MemoryEntry $memoryEntry): RedirectResponse
    {
        abort_unless($memoryEntry->user_id === $request->user()->id, 404);

        $memoryEntry->delete();

        return back()->with('status', 'Memory entry deleted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'content' => ['required', 'string'],
            'category' => ['required', 'string', 'max:80'],
            'is_pinned' => ['nullable', 'boolean'],
        ]) + [
            'is_pinned' => $request->boolean('is_pinned'),
        ];
    }
}
