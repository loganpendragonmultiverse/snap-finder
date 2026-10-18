@extends('layouts.app', ['title' => 'Dashboard | Snap Finder'])

@section('content')
    <section class="relative overflow-hidden pt-32 pb-28 sm:pt-40 sm:pb-36">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-primary">Listing Studio</h2>
                <p class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">My listings</p>
                <p class="mt-4 text-lg text-muted-foreground">Manage every Snapchat profile attached to your account.</p>
                <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                    <span class="rounded-full border {{ auth()->user()->hasVerifiedEmail() ? 'border-primary/30 bg-primary/10 text-primary' : 'border-border bg-secondary/50 text-muted-foreground' }} px-4 py-1.5 text-sm">
                        Email {{ auth()->user()->hasVerifiedEmail() ? 'verified' : 'not verified' }}
                    </span>
                    <span class="rounded-full border {{ auth()->user()->hasNsfwAccess() ? 'border-primary/30 bg-primary/10 text-primary' : 'border-border bg-secondary/50 text-muted-foreground' }} px-4 py-1.5 text-sm">
                        NSFW {{ auth()->user()->hasNsfwAccess() ? 'enabled' : 'not enabled' }}
                    </span>
                </div>
                <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    @if(auth()->user()->hasVerifiedEmail())
                        <a href="{{ route('profiles.create') }}" class="inline-flex h-12 items-center justify-center rounded-xl bg-primary px-6 text-sm font-medium text-primary-foreground">Create Listing</a>
                    @else
                        <a href="{{ route('verification.notice') }}" class="inline-flex h-12 items-center justify-center rounded-xl bg-primary px-6 text-sm font-medium text-primary-foreground">Verify Email To Post</a>
                    @endif
                    @unless(auth()->user()->hasNsfwAccess())
                        <a href="{{ route('age-gate.create', ['return' => route('dashboard')]) }}" class="inline-flex h-12 items-center justify-center rounded-xl border border-border bg-background px-6 text-sm font-medium transition-all hover:bg-secondary">Unlock NSFW Access</a>
                    @endunless
                </div>
            </div>

            <div class="mt-16 mb-20 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($profiles as $profile)
                    <article class="rounded-2xl border border-border/50 bg-card p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold">{{ $profile->display_name }}</h3>
                                <p class="text-sm text-muted-foreground">{{ '@'.$profile->snap_username }}</p>
                            </div>
                            @if($profile->paused_at)
                                <span class="rounded-full border border-border bg-secondary/50 px-3 py-1 text-xs text-muted-foreground">Paused</span>
                            @elseif($profile->is_adult)
                                <span class="rounded-full border border-primary/30 bg-primary/10 px-3 py-1 text-xs font-medium text-primary">NSFW</span>
                            @else
                                <span class="rounded-full border border-primary/30 bg-primary/10 px-3 py-1 text-xs font-medium text-primary">Live</span>
                            @endif
                        </div>
                        <p class="mt-4 text-sm text-muted-foreground">{{ $profile->headline }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach($profile->categories as $category)
                                <span class="rounded-full border border-border bg-secondary/50 px-3 py-1 text-xs text-muted-foreground">{{ $category->name }}</span>
                            @endforeach
                            @foreach($profile->tags->take(4) as $tag)
                                <span class="rounded-full border border-border bg-secondary/50 px-3 py-1 text-xs text-muted-foreground">#{{ $tag->name }}</span>
                            @endforeach
                        </div>
                        <p class="mt-4 text-xs text-muted-foreground">Reports: {{ $profile->reports_count }} · Updated {{ $profile->updated_at->format('M j, Y') }}</p>
                        @if($profile->status_reason)
                            <p class="mt-3 text-sm text-muted-foreground">Admin note: {{ $profile->status_reason }}</p>
                        @endif
                        <div class="mt-5 grid gap-2">
                            <a href="{{ route('profiles.edit', $profile) }}" class="inline-flex h-10 items-center justify-center rounded-xl bg-primary px-4 text-sm font-medium text-primary-foreground">Edit</a>
                            <form method="POST" action="{{ route('profiles.pause', $profile) }}">
                                @csrf
                                @method('PUT')
                                <button class="inline-flex h-10 w-full items-center justify-center rounded-xl border border-border bg-background px-4 text-sm font-medium transition-all hover:bg-secondary" type="submit">{{ $profile->paused_at ? 'Resume' : 'Pause' }}</button>
                            </form>
                            <form method="POST" action="{{ route('profiles.destroy', $profile) }}" onsubmit="return confirm('Delete this listing?')">
                                @csrf
                                @method('DELETE')
                                <button class="inline-flex h-10 w-full items-center justify-center rounded-xl border border-border bg-background px-4 text-sm font-medium transition-all hover:bg-secondary" type="submit">Delete</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <article class="my-3 rounded-2xl border border-border/50 bg-card p-9 sm:col-span-2 sm:p-10 lg:col-span-3">
                        <h3 class="text-xl font-semibold">No listings yet</h3>
                        <p class="mt-2 text-muted-foreground">Verify your email, then create your first Snapchat profile listing.</p>
                    </article>
                @endforelse
            </div>
        </div>
    </section>
@endsection
