<article class="rounded-2xl border border-border/50 bg-secondary/20 p-4">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="font-semibold">{{ $profile->display_name }}</p>
            <p class="text-sm text-muted-foreground">{{ '@'.$profile->snap_username }} by {{ $profile->user?->email }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <span class="rounded-full border px-3 py-1 text-xs {{ $statusClass($profile->status) }}">{{ ucfirst($profile->status) }}</span>
            <span class="rounded-full border border-border bg-card px-3 py-1 text-xs text-muted-foreground">{{ $profile->is_adult ? 'NSFW' : 'SFW' }}</span>
            @if ($profile->is_featured)
                <span class="rounded-full border border-primary/30 bg-primary/10 px-3 py-1 text-xs text-primary">Featured</span>
            @endif
            @if ($profile->reports_count)
                <span class="rounded-full border border-destructive/40 bg-destructive/10 px-3 py-1 text-xs text-destructive">{{ $profile->reports_count }} reports</span>
            @endif
        </div>
    </div>
    <p class="mt-3 text-sm text-muted-foreground">{{ $profile->headline }}</p>
    <div class="mt-3 flex flex-wrap gap-2 text-xs text-muted-foreground">
        @foreach ($profile->categories as $category)
            <span class="rounded-full border border-border bg-card px-3 py-1">{{ $category->name }}</span>
        @endforeach
        @foreach ($profile->tags as $tag)
            <span class="rounded-full border border-border bg-card px-3 py-1">#{{ $tag->name }}</span>
        @endforeach
    </div>
    <form method="POST" action="{{ route('admin.profiles.update', $profile) }}" class="mt-4 grid gap-4">
        @csrf
        @method('PUT')
        <div class="grid gap-3 md:grid-cols-[180px_1fr_auto]">
            <label class="sf-label">
                <span>Status</span>
                <select class="sf-field" name="status" required>
                    @foreach (['pending', 'approved', 'rejected', 'suspended'] as $status)
                        <option value="{{ $status }}" @selected($profile->status === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="sf-label"><span>Status reason</span><input class="sf-field" name="status_reason" value="{{ $profile->status_reason }}"></label>
            <div class="grid gap-2 text-sm text-muted-foreground">
                <label class="flex items-center gap-3"><input type="checkbox" name="is_featured" value="1" @checked($profile->is_featured)> Featured</label>
                <label class="flex items-center gap-3"><input type="checkbox" name="is_adult" value="1" @checked($profile->is_adult)> NSFW</label>
            </div>
        </div>
        <fieldset class="rounded-2xl border border-border/50 bg-card/40 p-4">
            <legend class="px-2 text-sm font-semibold text-primary">Categories</legend>
            <p class="mt-1 text-xs text-muted-foreground">At least one category is required.</p>
            <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($categories as $category)
                    <label class="flex items-center gap-3 text-sm text-muted-foreground">
                        <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" @checked($profile->categories->contains('id', $category->id))>
                        {{ $category->name }} @if($category->is_nsfw) (NSFW) @endif
                    </label>
                @endforeach
            </div>
        </fieldset>
        <div class="flex flex-wrap gap-3">
            <button type="submit" class="inline-flex h-10 items-center justify-center rounded-xl bg-primary px-4 text-sm font-medium text-primary-foreground transition-all hover:bg-primary/90">Save listing</button>
        </div>
    </form>
    <form method="POST" action="{{ route('admin.profiles.delete', $profile) }}" class="mt-3" onsubmit="return confirm('Delete this listing?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-sm text-destructive hover:underline">Delete listing</button>
    </form>
</article>
