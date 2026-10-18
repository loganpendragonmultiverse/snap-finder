@php
    use App\Support\MessageFormatter;
@endphp

<article class="message-bubble {{ $message->role === 'user' ? 'message-user' : 'message-assistant' }}" id="message-{{ $message->id }}">
    <div class="message-head">
        <div>
            <p class="message-role">{{ strtoupper($message->role) }}</p>
            <p class="message-meta">
                {{ $message->created_at->diffForHumans() }}
                @if($message->edited_at)
                    | edited
                @endif
            </p>
        </div>
        <div class="message-actions">
            <button type="button" class="chip-button" data-copy-message="{{ $message->id }}">Copy</button>
            <button type="button" class="chip-button" data-quote-message="{{ $message->id }}">Quote</button>
        </div>
    </div>

    <div class="message-content" data-message-content="{{ $message->id }}">{!! MessageFormatter::toHtml($message->content) !!}</div>

    @if (is_array(data_get($message->meta, 'generated_images')) && data_get($message->meta, 'generated_images'))
        <div class="generated-image-meta">
            <p class="meta-copy">
                {{ strtoupper(str_replace('_', ' ', (string) data_get($message->meta, 'generation_mode', 'standard'))) }}
                | {{ data_get(config('pendragon.image_models'), (string) $message->model.'.label', $message->model) }}
                | {{ data_get($message->meta, 'aspect_ratio', '1:1') }}
            </p>
            @if (data_get($message->meta, 'image_prompt'))
                <p class="meta-copy">Prompt: {{ data_get($message->meta, 'image_prompt') }}</p>
            @endif
        </div>

        <div class="generated-image-grid">
            @foreach (data_get($message->meta, 'generated_images', []) as $image)
                <a href="{{ data_get($image, 'url') }}" target="_blank" rel="noopener" class="generated-image-link">
                    <img src="{{ data_get($image, 'url') }}" alt="{{ data_get($message->meta, 'image_prompt', 'Generated chat image') }}" class="generated-image" />
                </a>
            @endforeach
        </div>
    @endif

    <details class="editor-toggle compact-form">
        <summary>Message Tools</summary>
        <form method="POST" action="{{ route('messages.update', $message) }}" class="stack-form compact-form">
            @csrf
            @method('PUT')
            <label>
                <span>Edit message</span>
                <textarea name="content" rows="4" required>{{ $message->content }}</textarea>
            </label>
            <button type="submit" class="secondary-button">Save Edit</button>
        </form>
        <form method="POST" action="{{ route('messages.destroy', $message) }}" class="compact-form" onsubmit="return confirm('Delete this message?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="danger-button">Delete Message</button>
        </form>
    </details>
</article>
