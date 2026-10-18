<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Services\AppSettingsService;
use App\Services\VeniceChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CharacterController extends Controller
{
    public function index(Request $request, VeniceChatService $venice, AppSettingsService $settings): View
    {
        $query = trim((string) $request->input('q'));

        $characters = Character::query()
            ->where('user_id', $request->user()->id)
            ->with(['user', 'memoryEntries', 'versions'])
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($nested) use ($query) {
                    $nested->where('name', 'like', '%'.$query.'%')
                        ->orWhere('short_description', 'like', '%'.$query.'%')
                        ->orWhere('tags', 'like', '%'.$query.'%')
                        ->orWhere('personality', 'like', '%'.$query.'%')
                        ->orWhereHas('memoryEntries', function ($memory) use ($query) {
                            $memory->where('title', 'like', '%'.$query.'%')
                                ->orWhere('content', 'like', '%'.$query.'%');
                        });
                });
            })
            ->orderByDesc('is_favorite')
            ->latest('updated_at')
            ->get();

        return view('characters.index', [
            'characters' => $characters,
            'modelOptions' => $venice->availableModels(),
            'defaults' => $settings->defaults(),
            'query' => $query,
        ]);
    }

    public function store(Request $request, AppSettingsService $settings): RedirectResponse
    {
        $data = $this->validated($request);
        $defaults = $settings->defaults();
        $data['user_id'] = $request->user()->id;
        $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(5));
        $data['is_active'] = true;
        $data['default_model'] = $data['default_model'] ?: $defaults['default_model'];
        $data['temperature'] = $data['temperature'] ?: $defaults['default_temperature'];
        $data['max_tokens'] = $data['max_tokens'] ?: $defaults['default_max_tokens'];
        $data['top_p'] = $data['top_p'] ?: $defaults['default_top_p'];

        $character = Character::create($data);
        $this->storeVersion($character, $request->user()->id, 'Initial version');

        return redirect()->route('characters.index')->with('status', 'Character created.');
    }

    public function import(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'character_json' => ['required', 'string'],
        ])['character_json'];

        $decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);

        $character = Character::create([
            'user_id' => $request->user()->id,
            'name' => data_get($decoded, 'name', 'Imported Character'),
            'slug' => Str::slug((string) data_get($decoded, 'name', 'imported-character')).'-'.Str::lower(Str::random(5)),
            'short_description' => data_get($decoded, 'short_description', 'Imported character'),
            'personality' => data_get($decoded, 'personality', ''),
            'scenario' => data_get($decoded, 'scenario', ''),
            'first_message' => data_get($decoded, 'first_message', 'Hello.'),
            'long_bio' => data_get($decoded, 'long_bio'),
            'writing_style' => data_get($decoded, 'writing_style'),
            'tone' => data_get($decoded, 'tone'),
            'tags' => data_get($decoded, 'tags'),
            'lore_notes' => data_get($decoded, 'lore_notes'),
            'default_model' => data_get($decoded, 'default_model', 'venice-uncensored-role-play'),
            'temperature' => (float) data_get($decoded, 'temperature', 0.9),
            'max_tokens' => (int) data_get($decoded, 'max_tokens', 900),
            'top_p' => (float) data_get($decoded, 'top_p', 0.95),
            'is_active' => true,
        ]);

        $this->storeVersion($character, $request->user()->id, 'Imported version');

        return redirect()->route('characters.index')->with('status', $character->name.' imported.');
    }

    public function favorite(Request $request, Character $character): RedirectResponse
    {
        abort_unless($character->user_id === $request->user()->id, 404);

        $character->update([
            'is_favorite' => ! $character->is_favorite,
        ]);

        return back()->with('status', $character->is_favorite ? 'Character favorited.' : 'Character unfavorited.');
    }

    public function export(Request $request, Character $character): JsonResponse
    {
        abort_unless($character->user_id === $request->user()->id, 404);

        return response()->json([
            'name' => $character->name,
            'short_description' => $character->short_description,
            'personality' => $character->personality,
            'scenario' => $character->scenario,
            'first_message' => $character->first_message,
            'long_bio' => $character->long_bio,
            'writing_style' => $character->writing_style,
            'tone' => $character->tone,
            'tags' => $character->tags,
            'lore_notes' => $character->lore_notes,
            'default_model' => $character->default_model,
            'temperature' => $character->temperature,
            'max_tokens' => $character->max_tokens,
            'top_p' => $character->top_p,
            'memory_entries' => $character->memoryEntries()->get(['title', 'content', 'category', 'is_pinned']),
        ]);
    }

    public function duplicate(Request $request, Character $character): RedirectResponse
    {
        abort_unless($character->user_id === $request->user()->id, 404);

        $duplicate = $character->duplicateForUser($request->user()->id);

        foreach ($character->memoryEntries as $entry) {
            $duplicate->memoryEntries()->create([
                'user_id' => $request->user()->id,
                'title' => $entry->title,
                'content' => $entry->content,
                'category' => $entry->category,
                'source' => $entry->source,
                'is_pinned' => $entry->is_pinned,
            ]);
        }

        $this->storeVersion($duplicate, $request->user()->id, 'Duplicated from '.$character->name);

        return redirect()->route('characters.index')->with('status', $duplicate->name.' created from template.');
    }

    public function update(Request $request, Character $character): RedirectResponse
    {
        abort_unless($character->user_id === $request->user()->id, 404);

        $data = $this->validated($request);
        $data['slug'] = $character->slug ?: Str::slug($data['name']).'-'.Str::lower(Str::random(5));

        $character->update($data);
        $this->storeVersion($character->fresh(), $request->user()->id, 'Saved changes');

        return back()->with('status', 'Character updated.');
    }

    public function destroy(Request $request, Character $character): RedirectResponse
    {
        abort_unless($character->user_id === $request->user()->id, 404);

        $character->delete();

        return redirect()->route('characters.index')->with('status', 'Character deleted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'short_description' => ['required', 'string', 'max:255'],
            'personality' => ['required', 'string'],
            'scenario' => ['required', 'string'],
            'first_message' => ['required', 'string'],
            'long_bio' => ['nullable', 'string'],
            'writing_style' => ['nullable', 'string', 'max:255'],
            'tone' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string', 'max:255'],
            'lore_notes' => ['nullable', 'string'],
            'default_model' => ['nullable', 'string', 'max:120'],
            'temperature' => ['nullable', 'numeric', 'between:0,2'],
            'max_tokens' => ['nullable', 'integer', 'min:128', 'max:4096'],
            'top_p' => ['nullable', 'numeric', 'between:0,1'],
        ]);
    }

    protected function storeVersion(Character $character, int $userId, string $label): void
    {
        $character->versions()->create([
            'user_id' => $userId,
            'label' => $label,
            'snapshot' => $character->snapshotPayload(),
        ]);
    }
}
