@extends('layouts.app', ['title' => $profile->display_name.' | Snap Finder'])

@section('content')
    <section class="relative overflow-hidden pt-32 pb-20 sm:pt-40 sm:pb-32">
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute top-1/4 left-1/2 h-[500px] w-[500px] -translate-x-1/2 rounded-full bg-primary/10 blur-[120px]"></div>
        </div>
        <div class="relative mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('directory.index') }}" class="mb-6 inline-flex h-10 items-center justify-center rounded-xl border border-border bg-background px-4 text-sm font-medium transition-all hover:bg-secondary">Back to Directory</a>
            <article class="rounded-3xl border border-primary/20 bg-card p-6 sm:p-10">
                <div class="flex flex-col gap-6 sm:flex-row sm:items-start">
                    <div class="sf-avatar-detail">
                        @if($profile->profile_image)
                            <img src="{{ $profile->profile_image }}" alt="{{ $profile->display_name }}" class="sf-avatar-img" referrerpolicy="no-referrer">
                        @else
                            @include('partials.ghost-icon', ['class' => 'h-16 w-16'])
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="sf-listing-meta sf-listing-meta-detail">
                            <p><span>Categories:</span> {{ $profile->categories->pluck('name')->join(', ') ?: 'Uncategorized' }}</p>
                            @if($profile->tags->isNotEmpty())
                                <p><span>Tags:</span> {{ $profile->tags->pluck('name')->join(', ') }}</p>
                            @endif
                            @if($profile->zip_code && $profile->categories->contains('slug', 'local'))
                                <p><span>Area:</span> {{ substr($profile->zip_code, 0, 3) }}xx</p>
                            @endif
                        </div>
                        <h1 class="mt-4 text-4xl font-bold tracking-tight sm:text-5xl">{{ $profile->display_name }}</h1>
                        <p class="mt-4 text-xl text-muted-foreground">{{ $profile->headline }}</p>
                    </div>
                </div>

                <div class="mt-8 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border border-border/50 bg-secondary/30 p-4">
                        <p class="text-sm text-muted-foreground">Snap username</p>
                        @if($canViewHandle)
                            <p class="mt-1 font-semibold">{{ '@'.$profile->snap_username }}</p>
                        @else
                            <a href="{{ route('login') }}" class="mt-2 inline-flex h-10 items-center justify-center rounded-xl bg-primary px-4 text-sm font-medium text-primary-foreground">Log in to view Snapchat</a>
                        @endif
                    </div>
                    <div class="rounded-2xl border border-border/50 bg-secondary/30 p-4">
                        <p class="text-sm text-muted-foreground">Looking for</p>
                        <p class="mt-1 font-semibold">{{ $profile->looking_for }}</p>
                    </div>
                </div>

                <p class="mt-8 text-base leading-relaxed text-muted-foreground">{{ $profile->bio }}</p>

                @auth
                    <form method="POST" action="{{ route('directory.save', $profile) }}" class="mt-6">
                        @csrf
                        <button class="inline-flex h-11 items-center justify-center rounded-xl border border-border bg-background px-5 text-sm font-medium transition-all hover:bg-secondary" type="submit">{{ $isSaved ? 'Remove from Saved' : 'Save Listing' }}</button>
                    </form>
                @endauth

                <details class="mt-8 rounded-2xl border border-border/50 bg-secondary/20 p-4">
                    <summary class="cursor-pointer font-semibold">Report this listing</summary>
                    <form method="POST" action="{{ route('directory.report', $profile) }}" class="mt-4 grid gap-4">
                        @csrf
                        <label class="sf-label"><span>Reason</span><input class="sf-field" name="reason" required placeholder="Spam, unsafe content, impersonation"></label>
                        <label class="sf-label"><span>Details</span><textarea class="sf-field" name="details" rows="4"></textarea></label>
                        <label class="sf-label"><span>Bot check: {{ $reportCaptcha }}</span><input class="sf-field" name="captcha_answer" required></label>
                        <button class="inline-flex h-11 items-center justify-center rounded-xl border border-border bg-background px-5 text-sm font-medium transition-all hover:bg-secondary" type="submit">Submit Report</button>
                    </form>
                </details>
            </article>
        </div>
    </section>
@endsection
