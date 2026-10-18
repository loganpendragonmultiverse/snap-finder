<section class="panel conversation-panel" data-chat-workspace data-active-chat-id="{{ optional($activeChat)->id }}">
    @if ($activeChat)
        <div class="conversation-header">
            <div>
                <p class="eyebrow">Active Thread</p>
                <h2>{{ $activeChat->title }}</h2>
                <p class="panel-copy">{{ $activeChat->character->name }} using {{ $modelOptions[$activeChat->model]['label'] ?? $activeChat->model }}</p>
            </div>
            <div class="button-row">
                <form method="POST" action="{{ route('chats.branch', $activeChat) }}">
                    @csrf
                    <button type="submit" class="ghost-button">Branch Chat</button>
                </form>
                <form method="POST" action="{{ route('messages.regenerate', $activeChat) }}" data-async="chat-shell">
                    @csrf
                    <button type="submit" class="ghost-button">Regenerate Reply</button>
                </form>
                @if (! $activeChat->is_archived)
                    <form method="POST" action="{{ route('chats.archive', $activeChat) }}" data-async="chat-shell">
                        @csrf
                        <button type="submit" class="ghost-button">Archive Chat</button>
                    </form>
                @endif
                <a href="{{ route('chats.export', $activeChat) }}" class="nav-link subtle-link">Export Chat</a>
            </div>
        </div>

        <div class="conversation-layout">
            <div class="message-column">
                <div class="conversation-toolbar">
                    <div class="conversation-toolbar-group">
                        <button type="button" class="chip-button active" data-panel-target="conversation">Conversation</button>
                        <button type="button" class="chip-button" data-panel-target="context">Context</button>
                        <button type="button" class="chip-button" data-panel-target="diagnostics">Diagnostics</button>
                    </div>
                    <div class="conversation-toolbar-group">
                        <button type="button" class="chip-button" data-autoscroll-toggle>Auto-scroll: on</button>
                        <button type="button" class="chip-button" data-scroll-bottom>Newest</button>
                    </div>
                </div>

                <div class="message-stream panel-stack" data-panel-section="conversation">
                    @foreach ($activeChat->messages as $message)
                        @include('chats.partials.message', ['message' => $message])
                    @endforeach
                </div>

                <div class="panel-stack" data-panel-section="context" hidden>
                    <section class="panel nested-panel">
                        <h3>Rolling Summary</h3>
                        <form method="POST" action="{{ route('summaries.update', $activeChat) }}" class="stack-form compact-form" data-async="chat-shell">
                            @csrf
                            @method('PUT')
                            <label><span>Auto Summary</span><textarea name="summary" rows="8">{{ optional($activeChat->summary)->summary }}</textarea></label>
                            <label><span>Manual Notes</span><textarea name="manual_notes" rows="6">{{ optional($activeChat->summary)->manual_notes }}</textarea></label>
                            <button type="submit" class="secondary-button">Save Summary</button>
                        </form>
                    </section>

                    <section class="panel nested-panel">
                        <div class="card-heading">
                            <h3>Chat Memory</h3>
                            <span class="tiny-pill">{{ $activeChat->memoryEntries->count() }} items</span>
                        </div>
                        <form method="POST" action="{{ route('memory.chat.store', $activeChat) }}" class="stack-form compact-form" data-async="chat-shell">
                            @csrf
                            <label><span>Title</span><input type="text" name="title" required></label>
                            <label><span>Category</span><input type="text" name="category" value="chat_fact" required></label>
                            <label><span>Memory</span><textarea name="content" rows="4" required></textarea></label>
                            <label class="checkbox-row">
                                <input type="checkbox" name="is_pinned" value="1">
                                <span>Pin this memory entry</span>
                            </label>
                            <button type="submit" class="secondary-button">Save Chat Memory</button>
                        </form>
                        <div class="log-stack compact-form">
                            @forelse ($activeChat->memoryEntries as $entry)
                                <article class="memory-card">
                                    <div class="card-heading">
                                        <div>
                                            <h4>{{ $entry->title }}</h4>
                                            <p>{{ $entry->category }}{{ $entry->is_pinned ? ' | pinned' : '' }}{{ $entry->source ? ' | '.$entry->source : '' }}</p>
                                        </div>
                                        <form method="POST" action="{{ route('memory.destroy', $entry) }}" onsubmit="return confirm('Delete this memory entry?');" data-async="chat-shell">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="danger-button">Delete</button>
                                        </form>
                                    </div>
                                    <div class="message-content"><p>{{ $entry->content }}</p></div>
                                </article>
                            @empty
                                <p class="empty-note">No saved chat memory yet.</p>
                            @endforelse
                        </div>
                    </section>
                </div>

                <div class="panel-stack" data-panel-section="diagnostics" hidden>
                    <section class="panel nested-panel">
                        <h3>Prompt Preview</h3>
                        <pre class="code-block">{{ $promptPreview }}</pre>
                    </section>
                    <section class="panel nested-panel">
                        <h3>Raw Turn Logs</h3>
                        <div class="log-stack">
                            @forelse ($activeChat->logs->take(8) as $log)
                                <details class="editor-toggle">
                                    <summary>{{ strtoupper($log->event_type) }}{{ $log->status_code ? ' | '.$log->status_code : '' }}</summary>
                                    <div class="stack-form compact-form">
                                        <label><span>Error</span><textarea rows="2" readonly>{{ $log->error_message }}</textarea></label>
                                        <label><span>Request Payload</span><textarea rows="8" readonly>{{ json_encode($log->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</textarea></label>
                                        <label><span>Response Payload</span><textarea rows="8" readonly>{{ json_encode($log->response_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</textarea></label>
                                    </div>
                                </details>
                            @empty
                                <p class="empty-note">No request logs yet.</p>
                            @endforelse
                        </div>
                    </section>
                </div>

                <form method="POST" action="{{ route('messages.store', $activeChat) }}" class="stack-form composer-card" data-async="chat-shell" data-chat-compose-form>
                    @csrf
                    <label>
                        <span>Next message</span>
                        <textarea id="chat-composer" name="content" rows="5" required placeholder="Continue the conversation, test memory, or try a different direction."></textarea>
                    </label>
                    <div class="button-row">
                        <button type="submit" class="primary-button" data-submit-label="Send To Venice">Send To Venice</button>
                        <span class="composer-hint">Shortcut: Ctrl/Cmd + Enter to send</span>
                    </div>
                </form>

                <section class="panel nested-panel">
                    <div class="card-heading">
                        <div>
                            <h3>Generate Images In Chat</h3>
                            <p class="panel-copy">Create standard images or in-character selfies and drop them straight into the conversation timeline.</p>
                        </div>
                        <span class="tiny-pill">Venice Images</span>
                    </div>

                    <form method="POST" action="{{ route('images.store', $activeChat) }}" class="stack-form compact-form" data-async="chat-shell">
                        @csrf
                        <div class="dual-grid">
                            <label>
                                <span>Mode</span>
                                <select name="mode" required>
                                    <option value="standard">Standard Image</option>
                                    <option value="character_selfie">Character Selfie</option>
                                </select>
                            </label>
                            <label>
                                <span>Image Model</span>
                                <select name="image_model" required>
                                    @foreach ($imageModelOptions as $modelId => $model)
                                        <option value="{{ $modelId }}" @selected($defaultImageModel === $modelId)>
                                            {{ $model['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>
                        </div>

                        <div class="dual-grid">
                            <label>
                                <span>Aspect Ratio</span>
                                <select name="aspect_ratio" required>
                                    <option value="1:1">1:1 Square</option>
                                    <option value="3:4">3:4 Portrait</option>
                                    <option value="4:3">4:3 Landscape</option>
                                    <option value="16:9">16:9 Wide</option>
                                </select>
                            </label>
                            <label>
                                <span>Negative Prompt</span>
                                <input type="text" name="negative_prompt" placeholder="Optional exclusions">
                            </label>
                        </div>

                        <label>
                            <span>Prompt</span>
                            <textarea name="prompt" rows="4" required placeholder="Describe the scene, pose, outfit, environment, style, or mood. In selfie mode this becomes the scene direction for the character."></textarea>
                        </label>

                        <button type="submit" class="secondary-button">Generate Image</button>
                    </form>
                </section>
            </div>

            <div class="memory-column">
                <section class="panel nested-panel">
                    <h3>Chat Settings</h3>
                    <form method="POST" action="{{ route('chats.update', $activeChat) }}" class="stack-form compact-form">
                        @csrf
                        @method('PUT')
                        <label><span>Title</span><input type="text" name="title" value="{{ $activeChat->title }}" required></label>
                        <label>
                            <span>Model</span>
                            <select name="model" required>
                                @foreach ($modelOptions as $modelId => $model)
                                    <option value="{{ $modelId }}" @selected($activeChat->model === $modelId) @disabled(($model['status'] ?? 'active') !== 'active')>
                                        {{ $model['label'] }}{{ ($model['status'] ?? 'active') !== 'active' ? ' (planned)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <div class="triple-grid">
                            <label><span>Temp</span><input type="number" name="temperature" step="0.01" min="0" max="2" value="{{ $activeChat->temperature }}" required></label>
                            <label><span>Max Tokens</span><input type="number" name="max_tokens" min="128" max="4096" value="{{ $activeChat->max_tokens }}" required></label>
                            <label><span>Top P</span><input type="number" name="top_p" step="0.01" min="0" max="1" value="{{ $activeChat->top_p }}" required></label>
                        </div>
                        <label>
                            <span>Folder</span>
                            <select name="chat_folder_id">
                                <option value="">No folder</option>
                                @foreach ($chatFolders as $folder)
                                    <option value="{{ $folder->id }}" @selected($activeChat->chat_folder_id === $folder->id)>{{ $folder->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label><span>Pinned Memory</span><textarea name="pinned_memory" rows="4">{{ $activeChat->pinned_memory }}</textarea></label>
                        <label class="checkbox-row">
                            <input type="checkbox" name="is_pinned" value="1" @checked($activeChat->is_pinned)>
                            <span>Pin this chat to the top</span>
                        </label>
                        <button type="submit" class="secondary-button">Update Chat</button>
                    </form>
                    <form method="POST" action="{{ route('chats.destroy', $activeChat) }}" class="compact-form" onsubmit="return confirm('Delete this chat thread?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="danger-button">Delete Chat</button>
                    </form>
                </section>
            </div>
        </div>
    @else
        <div class="empty-state">
            <p class="eyebrow">Ready For Work</p>
            <h2>No active chat yet</h2>
            <p class="panel-copy">Start a new chat from the left column, or head to Character Studio to build the roster first.</p>
        </div>
    @endif
</section>
