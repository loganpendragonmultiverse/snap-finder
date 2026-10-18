<aside class="panel sidebar-panel" data-chat-sidebar>
    <div class="panel-heading split-heading">
        <div>
            <p class="eyebrow">Threads</p>
            <h2>Chats</h2>
            <p class="panel-copy">Search, organize, and launch new threads without losing the active conversation.</p>
        </div>
        <span class="tiny-pill">{{ $chats->count() }} active</span>
    </div>

    <form method="GET" action="{{ route('chats.index') }}" class="stack-form compact-form">
        <label>
            <span>Search chats</span>
            <input type="text" name="q" value="{{ $query }}" placeholder="Search titles, characters, messages, memory">
        </label>
        <button type="submit" class="ghost-button">Search</button>
    </form>

    <section class="panel nested-panel compact-panel">
        <div class="card-heading">
            <div>
                <h3>Start New Chat</h3>
                <p class="panel-copy">Pick a character and open a fresh thread from the studio.</p>
            </div>
            <a href="{{ route('characters.index') }}" class="nav-link subtle-link">Character Studio</a>
        </div>
        <div class="character-list compact-list">
            @forelse ($characters as $character)
                <article class="character-card compact-card">
                    <div class="card-heading">
                        <div>
                            <h3>{{ $character->name }}</h3>
                            <p>{{ $character->short_description }}</p>
                        </div>
                        <span class="tiny-pill">{{ $modelOptions[$character->default_model]['label'] ?? $character->default_model }}</span>
                    </div>
                    <form method="POST" action="{{ route('chats.store', $character) }}" class="stack-form compact-form">
                        @csrf
                        <label>
                            <span>Thread title</span>
                            <input type="text" name="title" value="{{ $character->name }} Session" required>
                        </label>
                        <label>
                            <span>Folder</span>
                            <select name="chat_folder_id">
                                <option value="">No folder</option>
                                @foreach ($chatFolders as $folder)
                                    <option value="{{ $folder->id }}">{{ $folder->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <button type="submit" class="secondary-button">Start New Chat</button>
                    </form>
                </article>
            @empty
                <p class="empty-note">No characters yet. Create one in Character Studio first.</p>
            @endforelse
        </div>
    </section>

    <section class="panel nested-panel compact-panel">
        <div class="card-heading">
            <div>
                <h3>Active Threads</h3>
                <p class="panel-copy">Pinned chats stay at the top. Archived chats stay out of the way.</p>
            </div>
            @if ($activeChat)
                <span class="tiny-pill">Open: {{ $activeChat->title }}</span>
            @endif
        </div>
        <div class="chat-links">
            @forelse ($chats as $chat)
                <a href="{{ route('chats.show', $chat) }}" class="{{ optional($activeChat)->id === $chat->id ? 'chat-link active' : 'chat-link' }}">
                    <span>{{ $chat->title }}</span>
                    <small>{{ $chat->character->name }} | {{ $chat->updated_at->diffForHumans() }}{{ $chat->folder ? ' | '.$chat->folder->name : '' }}{{ $chat->is_pinned ? ' | pinned' : '' }}</small>
                </a>
            @empty
                <p class="empty-note">No chats yet. Start one from a character above.</p>
            @endforelse
        </div>
    </section>

    <section class="panel nested-panel compact-panel">
        <h3>Folders</h3>
        <form method="POST" action="{{ route('chat-folders.store') }}" class="stack-form compact-form">
            @csrf
            <label><span>Name</span><input type="text" name="name" required></label>
            <label><span>Description</span><input type="text" name="description"></label>
            <button type="submit" class="ghost-button">Create Folder</button>
        </form>
        <div class="log-stack compact-form">
            @forelse ($chatFolders as $folder)
                <article class="memory-card">
                    <div class="card-heading">
                        <div>
                            <h4>{{ $folder->name }}</h4>
                            <p>{{ $folder->description ?: 'No description yet.' }}</p>
                            <p class="meta-copy">{{ $folder->chats_count }} active chats{{ $folder->is_favorite ? ' | favorite' : '' }}</p>
                        </div>
                        <div class="button-row">
                            <form method="POST" action="{{ route('chat-folders.favorite', $folder) }}" data-async="chat-shell">
                                @csrf
                                <button type="submit" class="ghost-button">{{ $folder->is_favorite ? 'Unfavorite' : 'Favorite' }}</button>
                            </form>
                            <form method="POST" action="{{ route('chat-folders.destroy', $folder) }}" onsubmit="return confirm('Delete this folder? Chats will be left in place without a folder.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="danger-button">Delete</button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <p class="empty-note">No folders yet.</p>
            @endforelse
        </div>
    </section>

    <section class="panel nested-panel compact-panel">
        <h3>Archived Chats</h3>
        <div class="log-stack compact-form">
            @forelse ($archivedChats as $chat)
                <article class="memory-card">
                    <div class="card-heading">
                        <div>
                            <h4>{{ $chat->title }}</h4>
                            <p>{{ $chat->character->name }}{{ $chat->folder ? ' | '.$chat->folder->name : '' }}{{ $chat->archived_at ? ' | archived '.$chat->archived_at->diffForHumans() : '' }}</p>
                        </div>
                        <form method="POST" action="{{ route('chats.restore', $chat) }}" data-async="chat-shell">
                            @csrf
                            <button type="submit" class="ghost-button">Restore</button>
                        </form>
                    </div>
                </article>
            @empty
                <p class="empty-note">No archived chats yet.</p>
            @endforelse
        </div>
    </section>
</aside>
