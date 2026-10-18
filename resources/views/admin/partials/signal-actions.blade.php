<div class="mt-3 flex flex-wrap gap-2">
    <form method="POST" action="{{ route('admin.profiles.update', $profile) }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="status" value="suspended">
        <input type="hidden" name="status_reason" value="Suspended from spam signal review.">
        <input type="hidden" name="is_featured" value="{{ $profile->is_featured ? 1 : 0 }}">
        <input type="hidden" name="is_adult" value="{{ $profile->is_adult ? 1 : 0 }}">
        @foreach($profile->categories as $category)
            <input type="hidden" name="category_ids[]" value="{{ $category->id }}">
        @endforeach
        <button class="inline-flex h-9 items-center justify-center rounded-xl border border-destructive/40 bg-destructive/10 px-3 text-xs font-medium text-destructive" type="submit">Suspend</button>
    </form>
    <form method="POST" action="{{ route('admin.profiles.update', $profile) }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="status" value="{{ $profile->status }}">
        <input type="hidden" name="status_reason" value="Marked NSFW from spam signal review.">
        <input type="hidden" name="is_featured" value="{{ $profile->is_featured ? 1 : 0 }}">
        <input type="hidden" name="is_adult" value="1">
        @foreach($profile->categories as $category)
            <input type="hidden" name="category_ids[]" value="{{ $category->id }}">
        @endforeach
        <button class="inline-flex h-9 items-center justify-center rounded-xl border border-primary/30 bg-primary/10 px-3 text-xs font-medium text-primary" type="submit">Mark NSFW</button>
    </form>
    <a href="{{ route('directory.show', $profile) }}" class="inline-flex h-9 items-center justify-center rounded-xl border border-border bg-background px-3 text-xs font-medium transition-all hover:bg-secondary">View</a>
    <form method="POST" action="{{ route('admin.profiles.delete', $profile) }}" onsubmit="return confirm('Delete this listing?')">
        @csrf
        @method('DELETE')
        <button class="inline-flex h-9 items-center justify-center rounded-xl border border-border bg-background px-3 text-xs font-medium text-destructive transition-all hover:bg-secondary" type="submit">Delete</button>
    </form>
</div>
