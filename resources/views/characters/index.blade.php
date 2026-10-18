@extends('layouts.app', ['title' => 'Characters | Pendragon Chat Bot'])

@section('content')
    <main class="stack-layout">
        <section class="panel">
            <div class="panel-heading split-heading">
                <div>
                    <p class="eyebrow">Character Lab</p>
                    <h2>Characters</h2>
                    <p class="panel-copy">Create, edit, duplicate, export, import, and manage the roster separately from chats.</p>
                </div>
                <span class="tiny-pill">{{ $characters->count() }} rostered</span>
            </div>

            <div class="dual-grid">
                <section class="panel nested-panel">
                    <h3>Create Character</h3>
                    <form method="POST" action="{{ route('characters.store') }}" class="stack-form compact-form">
                        @csrf
                        <label><span>Name</span><input type="text" name="name" required></label>
                        <label><span>Short Description</span><input type="text" name="short_description" required></label>
                        <label><span>Personality</span><textarea name="personality" rows="3" required></textarea></label>
                        <label><span>Scenario</span><textarea name="scenario" rows="3" required></textarea></label>
                        <label><span>First Message</span><textarea name="first_message" rows="4" required></textarea></label>
                        <label><span>Long Bio</span><textarea name="long_bio" rows="3"></textarea></label>
                        <label><span>Writing Style</span><input type="text" name="writing_style"></label>
                        <label><span>Tone</span><input type="text" name="tone"></label>
                        <label><span>Tags</span><input type="text" name="tags" placeholder="fantasy, uncensored, companion"></label>
                        <label><span>Lore Notes</span><textarea name="lore_notes" rows="3"></textarea></label>
                        <label>
                            <span>Default Model</span>
                            <select name="default_model" required>
                                @foreach ($modelOptions as $modelId => $model)
                                    <option value="{{ $modelId }}" @selected($defaults['default_model'] === $modelId) @disabled(($model['status'] ?? 'active') !== 'active')>
                                        {{ $model['label'] }}{{ ($model['status'] ?? 'active') !== 'active' ? ' (planned)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <div class="triple-grid">
                            <label><span>Temp</span><input type="number" name="temperature" step="0.01" min="0" max="2" value="{{ $defaults['default_temperature'] }}" required></label>
                            <label><span>Max Tokens</span><input type="number" name="max_tokens" min="128" max="4096" value="{{ $defaults['default_max_tokens'] }}" required></label>
                            <label><span>Top P</span><input type="number" name="top_p" step="0.01" min="0" max="1" value="{{ $defaults['default_top_p'] }}" required></label>
                        </div>
                        <button type="submit" class="primary-button">Save Character</button>
                    </form>
                </section>

                <section class="panel nested-panel">
                    <h3>Import / Search</h3>
                    <form method="GET" action="{{ route('characters.index') }}" class="stack-form compact-form">
                        <label>
                            <span>Search characters</span>
                            <input type="text" name="q" value="{{ $query }}" placeholder="Search character fields and memory">
                        </label>
                        <button type="submit" class="ghost-button">Search</button>
                    </form>

                    <form method="POST" action="{{ route('characters.import') }}" class="stack-form compact-form">
                        @csrf
                        <label>
                            <span>Import JSON</span>
                            <textarea name="character_json" rows="14" placeholder='{"name":"Pendragon","short_description":"..."}' required></textarea>
                        </label>
                        <button type="submit" class="secondary-button">Import Character</button>
                    </form>

                    <form method="POST" action="{{ route('characters.card.import') }}" class="stack-form compact-form">
                        @csrf
                        <label>
                            <span>Import Character Card</span>
                            <textarea name="character_card_json" rows="12" placeholder='{"card_version":"pendragon-character-card.v1","character":{...}}' required></textarea>
                        </label>
                        <button type="submit" class="ghost-button">Import Card Package</button>
                    </form>
                </section>
            </div>
        </section>

        <section class="panel">
            <div class="panel-heading split-heading">
                <div>
                    <p class="eyebrow">Roster</p>
                    <h2>Saved Characters</h2>
                </div>
                <span class="tiny-pill">{{ $characters->count() }} entries</span>
            </div>

            <div class="character-list">
                @forelse ($characters as $character)
                    <article class="character-card">
                        <div class="card-heading">
                            <div>
                                <h3>{{ $character->name }}</h3>
                                <p>{{ $character->short_description }}</p>
                                <p class="meta-copy">Created by {{ $character->user->name ?? $character->user->email ?? 'Unknown' }} | {{ $character->updated_at->diffForHumans() }}{{ $character->is_favorite ? ' | favorite' : '' }}</p>
                            </div>
                            <span class="tiny-pill">{{ $modelOptions[$character->default_model]['label'] ?? $character->default_model }}</span>
                        </div>

                        <div class="button-row compact-form">
                            <form method="POST" action="{{ route('characters.favorite', $character) }}">
                                @csrf
                                <button type="submit" class="ghost-button">{{ $character->is_favorite ? 'Unfavorite' : 'Favorite' }}</button>
                            </form>
                            <form method="POST" action="{{ route('chats.store', $character) }}">
                                @csrf
                                <input type="hidden" name="title" value="{{ $character->name }} Session">
                                <button type="submit" class="secondary-button">Start New Chat</button>
                            </form>
                            <form method="POST" action="{{ route('characters.duplicate', $character) }}">
                                @csrf
                                <button type="submit" class="ghost-button">Duplicate</button>
                            </form>
                            <a href="{{ route('characters.export', $character) }}" class="nav-link subtle-link">Export JSON</a>
                            <a href="{{ route('characters.card.export', $character) }}" class="nav-link subtle-link">Export Card</a>
                        </div>

                        <div class="dual-grid compact-form">
                            <section class="nested-panel">
                                <h4>Edit Character</h4>
                                <form method="POST" action="{{ route('characters.update', $character) }}" class="stack-form compact-form">
                                    @csrf
                                    @method('PUT')
                                    <label><span>Name</span><input type="text" name="name" value="{{ $character->name }}" required></label>
                                    <label><span>Short Description</span><input type="text" name="short_description" value="{{ $character->short_description }}" required></label>
                                    <label><span>Personality</span><textarea name="personality" rows="3" required>{{ $character->personality }}</textarea></label>
                                    <label><span>Scenario</span><textarea name="scenario" rows="3" required>{{ $character->scenario }}</textarea></label>
                                    <label><span>First Message</span><textarea name="first_message" rows="4" required>{{ $character->first_message }}</textarea></label>
                                    <label><span>Long Bio</span><textarea name="long_bio" rows="3">{{ $character->long_bio }}</textarea></label>
                                    <label><span>Writing Style</span><input type="text" name="writing_style" value="{{ $character->writing_style }}"></label>
                                    <label><span>Tone</span><input type="text" name="tone" value="{{ $character->tone }}"></label>
                                    <label><span>Tags</span><input type="text" name="tags" value="{{ $character->tags }}"></label>
                                    <label><span>Lore Notes</span><textarea name="lore_notes" rows="3">{{ $character->lore_notes }}</textarea></label>
                                    <label>
                                        <span>Default Model</span>
                                        <select name="default_model" required>
                                            @foreach ($modelOptions as $modelId => $model)
                                                <option value="{{ $modelId }}" @selected($character->default_model === $modelId) @disabled(($model['status'] ?? 'active') !== 'active')>
                                                    {{ $model['label'] }}{{ ($model['status'] ?? 'active') !== 'active' ? ' (planned)' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <div class="triple-grid">
                                        <label><span>Temp</span><input type="number" name="temperature" step="0.01" min="0" max="2" value="{{ $character->temperature }}" required></label>
                                        <label><span>Max Tokens</span><input type="number" name="max_tokens" min="128" max="4096" value="{{ $character->max_tokens }}" required></label>
                                        <label><span>Top P</span><input type="number" name="top_p" step="0.01" min="0" max="1" value="{{ $character->top_p }}" required></label>
                                    </div>
                                    <button type="submit" class="secondary-button">Update Character</button>
                                </form>

                                <form method="POST" action="{{ route('characters.destroy', $character) }}" class="compact-form" onsubmit="return confirm('Delete this character and related chats?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="danger-button">Delete Character</button>
                                </form>
                            </section>

                            <section class="nested-panel">
                                <h4>Character Memory</h4>
                                <form method="POST" action="{{ route('memory.character.store', $character) }}" class="stack-form compact-form">
                                    @csrf
                                    <label><span>Title</span><input type="text" name="title" required></label>
                                    <label><span>Category</span><input type="text" name="category" value="character_fact" required></label>
                                    <label><span>Memory</span><textarea name="content" rows="4" required></textarea></label>
                                    <label class="checkbox-row">
                                        <input type="checkbox" name="is_pinned" value="1">
                                        <span>Pin this memory</span>
                                    </label>
                                    <button type="submit" class="secondary-button">Save Character Memory</button>
                                </form>

                                <div class="log-stack compact-form">
                                    @forelse ($character->memoryEntries as $entry)
                                        <article class="memory-card">
                                            <div class="card-heading">
                                                <div>
                                                    <h4>{{ $entry->title }}</h4>
                                                    <p>{{ $entry->category }}{{ $entry->is_pinned ? ' | pinned' : '' }}{{ $entry->source ? ' | '.$entry->source : '' }}</p>
                                                </div>
                                                <form method="POST" action="{{ route('memory.destroy', $entry) }}" onsubmit="return confirm('Delete this memory entry?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="danger-button">Delete</button>
                                                </form>
                                            </div>
                                            <div class="message-content">{{ $entry->content }}</div>
                                        </article>
                                    @empty
                                        <p class="empty-note">No character memory saved yet.</p>
                                    @endforelse
                                </div>
                            </section>

                            <section class="nested-panel">
                                <h4>Version History</h4>
                                <div class="log-stack compact-form">
                                    @forelse ($character->versions->take(8) as $version)
                                        <article class="memory-card">
                                            <div class="card-heading">
                                                <div>
                                                    <h4>{{ $version->label ?: 'Saved version' }}</h4>
                                                    <p>{{ $version->created_at->diffForHumans() }}</p>
                                                </div>
                                                <a href="{{ route('characters.versions.show', [$character, $version]) }}" class="nav-link subtle-link">Preview</a>
                                            </div>
                                            <div class="message-content">{{ data_get($version->snapshot, 'short_description', 'No summary stored.') }}</div>
                                        </article>
                                    @empty
                                        <p class="empty-note">No saved versions yet.</p>
                                    @endforelse
                                </div>
                            </section>
                        </div>
                    </article>
                @empty
                    <p class="empty-note">No characters yet. Build the first one above.</p>
                @endforelse
            </div>
        </section>
    </main>
@endsection
