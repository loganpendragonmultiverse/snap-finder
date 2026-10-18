@extends('layouts.app', [
    'title' => $seoContent['title'] ?? ($activeCategoryModel ? $activeCategoryModel->name.' Snapchat Directory | Snap Finder' : 'Directory | Snap Finder'),
    'description' => $seoContent['description'] ?? ($activeCategoryModel ? 'Browse public Snap Finder listings in the '.$activeCategoryModel->name.' category.' : 'Search public Snap Finder listings by category, keyword, or zip code.'),
    'canonicalUrl' => $canonicalUrl ?? route('directory.index'),
    'robotsMeta' => $robotsMeta ?? null,
    'structuredData' => $seoContent ? [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'CollectionPage',
                'name' => $seoContent['heading'],
                'description' => $seoContent['description'],
                'url' => $canonicalUrl ?? route('directory.index'),
                'about' => $seoContent['about'],
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => array_values(array_filter([
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Snap Finder', 'item' => route('welcome')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Directory', 'item' => route('directory.index')],
                    $activeCategoryModel ? ['@type' => 'ListItem', 'position' => 3, 'name' => $activeCategoryModel->name, 'item' => $canonicalUrl] : null,
                ])),
            ],
        ],
    ] : null,
])

@section('content')
    @php
        $listingCount = method_exists($profiles, 'total') ? $profiles->total() : $profiles->count();
        $hasFilters = $search !== '' || $zipCode !== '' || $activeCategory !== '';
    @endphp

    <section class="sf-directory-intro relative overflow-hidden pb-7 pt-24 sm:pt-28">
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute left-1/2 top-0 h-[420px] w-[680px] -translate-x-1/2 rounded-full bg-primary/10 blur-[130px]"></div>
        </div>
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <div class="inline-flex items-center gap-2 rounded-full border border-primary/25 bg-primary/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-primary">
                    <span>Directory</span><span class="h-1 w-1 rounded-full bg-primary" aria-hidden="true"></span><span>{{ $listingCount }} live {{ $listingCount === 1 ? 'listing' : 'listings' }}</span>
                </div>
                <h1 class="mt-4 text-3xl font-bold tracking-tight sm:text-4xl">{{ $activeCategoryModel ? $activeCategoryModel->name.' Snapchat directory' : 'Find Snapchat people by what you have in common' }}</h1>
                <p class="mx-auto mt-4 max-w-2xl text-base leading-relaxed text-muted-foreground sm:text-lg">Search by interest, category, or general area. Every listing is submitted by the person behind it and includes context before you connect.</p>
            </div>

            <div class="sf-directory-search mx-auto mt-7 max-w-6xl rounded-3xl border border-border/70 bg-card/90 p-4 shadow-xl shadow-black/20 sm:p-6">
                <form method="GET" action="{{ route('directory.index') }}" class="sf-filter" data-ga-event="search" data-ga-label="Snap Finder directory search">
                    @if ($activeCategory !== '')<input type="hidden" name="category" value="{{ $activeCategory }}">@endif
                    @if ($showingNsfw)<input type="hidden" name="nsfw" value="1">@endif
                    <label class="sf-label"><span>Interest or keyword</span><input class="sf-field" type="search" name="q" value="{{ $search }}" placeholder="Gaming, photography, fitness..."></label>
                    <label class="sf-label"><span>ZIP code <small>(optional)</small></span><input class="sf-field" type="text" name="zip" value="{{ $zipCode }}" inputmode="numeric" autocomplete="postal-code" placeholder="General area"></label>
                    <label class="sf-label">
                        <span>Sort</span>
                        <select class="sf-field" name="sort">
                            <option value="newest" @selected($sort === 'newest')>Newest first</option>
                            <option value="oldest" @selected($sort === 'oldest')>Oldest first</option>
                            <option value="name" @selected($sort === 'name')>Name</option>
                        </select>
                    </label>
                    <div class="flex items-end gap-2">
                        <button class="inline-flex h-12 flex-1 items-center justify-center whitespace-nowrap rounded-xl bg-primary px-6 text-sm font-semibold text-primary-foreground transition-all hover:bg-primary/90" type="submit">Search listings</button>
                        @if ($search !== '' || $zipCode !== '')<a href="{{ route('directory.index', $activeCategory === '' ? [] : ['category' => $activeCategory] + ($activeCategory === 'nsfw' ? ['age' => '21'] : [])) }}" class="inline-flex h-12 items-center justify-center whitespace-nowrap rounded-xl border border-border bg-background px-4 text-sm font-medium transition-all hover:bg-secondary">Clear</a>@endif
                    </div>
                </form>

                <div class="mt-5 border-t border-border/60 pt-5">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-muted-foreground">Browse by connection</p>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('directory.index', array_filter(['q' => $search, 'zip' => $zipCode])) }}" class="rounded-full border border-border bg-secondary/50 px-4 py-2 text-sm transition-colors hover:border-primary/50 hover:text-primary {{ $activeCategory === '' ? 'border-primary/50 bg-primary/10 text-primary' : '' }}">All</a>
                        @foreach ($categories as $category)
                            @php
                                $categoryUrl = ($search === '' && $zipCode === '' && ! $category->is_nsfw)
                                    ? route('directory.category', $category->slug)
                                    : route('directory.index', array_filter(['category' => $category->slug, 'q' => $search, 'zip' => $zipCode]) + ($category->is_nsfw ? ['nsfw' => 1] : []));
                            @endphp
                            <a href="{{ $categoryUrl }}" class="rounded-full border border-border bg-secondary/50 px-4 py-2 text-sm transition-colors hover:border-primary/50 hover:text-primary {{ $activeCategory === $category->slug ? 'border-primary/50 bg-primary/10 text-primary' : '' }}">{{ $category->name }}</a>
                        @endforeach
                        @unless($categories->contains('is_nsfw', true))<a href="{{ route('directory.index', ['category' => 'nsfw', 'nsfw' => 1]) }}" class="rounded-full border border-primary/30 bg-primary/10 px-4 py-2 text-sm text-primary transition-colors hover:border-primary/60 hover:bg-primary/20 {{ $showingNsfw ? 'border-primary/60 bg-primary/20' : '' }}">NSFW</a>@endunless
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="sf-results-section relative pb-16 sm:pb-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if($nsfwAccessMessage)
                <div class="mx-auto mb-8 max-w-4xl rounded-2xl border border-primary/30 bg-primary/10 p-5 text-center">
                    <h2 class="text-lg font-semibold">NSFW access check</h2>
                    <p class="mx-auto mt-2 max-w-2xl text-sm leading-relaxed text-muted-foreground">Adult Creators, After Dark, and NSFW listings stay hidden until your account is logged in, email verified, and 18+ access is enabled.</p>
                    <div class="mt-4 flex flex-wrap justify-center gap-3">
                        @guest
                            <a href="{{ route('login') }}" class="inline-flex h-11 items-center justify-center rounded-xl bg-primary px-5 text-sm font-medium text-primary-foreground">Log in</a>
                            <a href="{{ route('register') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-border bg-background px-5 text-sm font-medium transition-all hover:bg-secondary">Register</a>
                        @else
                            @unless(auth()->user()->hasVerifiedEmail())<a href="{{ route('verification.notice') }}" class="inline-flex h-11 items-center justify-center rounded-xl bg-primary px-5 text-sm font-medium text-primary-foreground">Verify email</a>@endunless
                            @if(auth()->user()->hasVerifiedEmail() && ! auth()->user()->hasNsfwAccess())<a href="{{ route('age-gate.create', ['return' => request()->fullUrl()]) }}" class="inline-flex h-11 items-center justify-center rounded-xl bg-primary px-5 text-sm font-medium text-primary-foreground">Enable NSFW access</a>@endif
                        @endguest
                    </div>
                </div>
            @endif

            @if($profiles->isEmpty() && ! $nsfwAccessMessage)
                @if($hasFilters)
                    <article class="mx-auto max-w-4xl rounded-3xl border border-border/60 bg-card p-8 text-center sm:p-10">
                        <p class="text-sm font-semibold uppercase tracking-wider text-primary">Nothing matched yet</p>
                        <h2 class="mt-3 text-2xl font-semibold sm:text-3xl">Try a wider search</h2>
                        <p class="mx-auto mt-3 max-w-xl text-muted-foreground">Remove the ZIP code, choose a broader category, or search for one interest at a time.</p>
                        <a href="{{ route('directory.index') }}" class="mt-6 inline-flex h-11 items-center justify-center rounded-xl bg-primary px-6 text-sm font-semibold text-primary-foreground">Clear all filters</a>
                    </article>
                @else
                    <article class="sf-empty-activation mx-auto max-w-6xl overflow-hidden rounded-3xl border border-primary/20 bg-card">
                        <div class="sf-empty-layout">
                            <div class="sf-empty-copy">
                                <p class="text-sm font-semibold uppercase tracking-wider text-primary">Founding listings are open</p>
                                <h2 class="mt-3 max-w-2xl text-2xl font-bold tracking-tight sm:text-3xl">Be one of the first people others can actually find</h2>
                                <p class="mt-5 max-w-2xl text-base leading-relaxed text-muted-foreground sm:text-lg">Snap Finder is new and the public directory is still empty. A clear listing takes a few minutes and goes live after account verification and automated safety screening.</p>
                                <div class="mt-7 flex flex-wrap gap-3">
                                    @auth
                                        <a href="{{ route('profiles.create') }}" data-ga-event="begin_listing" data-ga-label="Directory founding listing" class="inline-flex h-12 items-center justify-center rounded-xl bg-primary px-6 text-sm font-semibold text-primary-foreground">Create the first listing</a>
                                        <a href="{{ route('dashboard') }}" class="inline-flex h-12 items-center justify-center rounded-xl border border-border bg-background px-6 text-sm font-semibold hover:bg-secondary">Go to dashboard</a>
                                    @else
                                        <a href="{{ route('register') }}" data-ga-event="sign_up_intent" data-ga-label="Directory founding listing" class="inline-flex h-12 items-center justify-center rounded-xl bg-primary px-6 text-sm font-semibold text-primary-foreground">Create a free listing</a>
                                        <a href="{{ route('blog.index') }}" class="inline-flex h-12 items-center justify-center rounded-xl border border-border bg-background px-6 text-sm font-semibold hover:bg-secondary">Read the blog</a>
                                    @endauth
                                </div>
                                <p class="mt-5 text-sm text-muted-foreground">Independent directory. Not affiliated with Snap Inc. Never share passwords, login codes, or private location details.</p>
                            </div>
                            <div class="sf-empty-steps">
                                <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">How it works</p>
                                <ol class="mt-5 space-y-5">
                                    <li class="flex gap-4"><span class="sf-step-number">1</span><div><strong class="block">Create an account</strong><span class="mt-1 block text-sm leading-relaxed text-muted-foreground">Use a working email so your listing stays under your control.</span></div></li>
                                    <li class="flex gap-4"><span class="sf-step-number">2</span><div><strong class="block">Describe the connection</strong><span class="mt-1 block text-sm leading-relaxed text-muted-foreground">Add interests, a short bio, and what you are looking for.</span></div></li>
                                    <li class="flex gap-4"><span class="sf-step-number">3</span><div><strong class="block">Publish and stay in control</strong><span class="mt-1 block text-sm leading-relaxed text-muted-foreground">Pause, edit, or delete your listing whenever you want.</span></div></li>
                                </ol>
                            </div>
                        </div>
                    </article>

                    @if($starterPosts->isNotEmpty())
                        <section class="mx-auto mt-12 max-w-6xl" aria-labelledby="directory-blog-heading">
                            <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                                <div><p class="text-sm font-semibold uppercase tracking-wider text-primary">From the blog</p><h2 id="directory-blog-heading" class="mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">Make a better first connection</h2></div>
                                <a href="{{ route('blog.index') }}" class="text-sm font-semibold text-primary hover:underline">View all articles →</a>
                            </div>
                            <div class="sf-blog-preview-grid">
                                @foreach($starterPosts as $post)
                                    <a href="{{ route('blog.show', $post['slug']) }}" class="sf-blog-preview-card group rounded-2xl border border-border/60 bg-card transition-all hover:-translate-y-1 hover:border-primary/35">
                                        <span class="text-xs font-semibold uppercase tracking-wider text-primary">{{ $post['category'] }}</span>
                                        <h3 class="mt-3 text-lg font-semibold leading-snug group-hover:text-primary">{{ $post['title'] }}</h3>
                                        <p class="mt-3 line-clamp-3 text-sm leading-relaxed text-muted-foreground">{{ $post['excerpt'] }}</p>
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endif
                @endif
            @else
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($profiles as $profile)
                        <a href="{{ route('directory.show', $profile) }}" class="sf-listing-card sf-card-link group relative rounded-2xl border border-border/50 bg-card p-5 transition-all hover:border-primary/30 hover:bg-card/80">
                            <div class="sf-listing-card-head">
                                <div class="sf-avatar-card transition-colors group-hover:border-primary/30">@if($profile->profile_image)<img src="{{ $profile->profile_image }}" alt="{{ $profile->display_name }}" class="sf-avatar-img" loading="lazy" referrerpolicy="no-referrer">@else @include('partials.ghost-icon', ['class' => 'h-10 w-10']) @endif</div>
                                <div class="min-w-0 pt-2"><h2 class="text-xl font-semibold leading-tight">{{ $profile->display_name }}</h2><p class="mt-1 text-sm text-muted-foreground">@auth {{ '@'.$profile->snap_username }} @else Log in to view Snapchat @endauth</p></div>
                                <div class="sf-listing-card-badges flex shrink-0 flex-col items-end gap-2">@if ($profile->is_adult)<span class="rounded-full border border-primary/30 bg-primary/10 px-3 py-1 text-xs font-medium text-primary">NSFW</span>@endif @if ($profile->is_featured)<span class="rounded-full border border-primary/30 bg-primary/10 px-3 py-1 text-xs font-medium text-primary">Featured</span>@endif</div>
                            </div>
                            <p class="mt-4 text-sm font-medium text-foreground">{{ $profile->headline }}</p>
                            <p class="mt-3 line-clamp-3 text-sm leading-relaxed text-muted-foreground">{{ $profile->bio }}</p>
                            <div class="sf-listing-meta"><p><span>Categories:</span> {{ $profile->categories->pluck('name')->join(', ') ?: 'Uncategorized' }}</p>@if($profile->tags->isNotEmpty())<p><span>Tags:</span> {{ $profile->tags->take(6)->pluck('name')->join(', ') }}</p>@endif @if($profile->zip_code && $profile->categories->contains('slug', 'local'))<p><span>Area:</span> {{ substr($profile->zip_code, 0, 3) }}xx</p>@endif</div>
                        </a>
                    @endforeach
                </div>
                @if(method_exists($profiles, 'links'))<div class="mt-10">{{ $profiles->links() }}</div>@endif
            @endif

            @if($seoContent)
                <details class="sf-directory-guide mx-auto mt-10 max-w-6xl rounded-2xl border border-border/60 bg-background/50">
                    <summary class="cursor-pointer px-6 py-5 text-base font-semibold text-foreground sm:px-8">About this Snapchat directory and safer browsing</summary>
                    <div class="grid gap-8 border-t border-border/60 px-6 py-7 sm:px-8 lg:grid-cols-[1fr_.85fr]">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-primary">About this directory</p>
                            <h2 class="mt-3 text-2xl font-semibold tracking-tight sm:text-3xl">{{ $seoContent['heading'] }}</h2>
                            @foreach($seoContent['intro'] as $paragraph)<p class="mt-4 leading-relaxed text-muted-foreground">{{ $paragraph }}</p>@endforeach
                            <p class="mt-5 text-sm text-muted-foreground">Review the <a class="text-primary hover:underline" href="{{ route('community-guidelines') }}">Community Guidelines</a>, visit the <a class="text-primary hover:underline" href="{{ route('blog.index') }}">Snap Finder Blog</a>, or use the <a class="text-primary hover:underline" href="{{ route('contact') }}">contact page</a> when you need help.</p>
                        </div>
                        <div><h3 class="text-lg font-semibold">Browse with intention</h3><ul class="mt-4 grid gap-3 text-sm leading-relaxed text-muted-foreground">@foreach($seoContent['tips'] as $tip)<li class="rounded-xl border border-border/50 bg-card p-4">{{ $tip }}</li>@endforeach</ul></div>
                    </div>
                </details>
            @endif
        </div>
    </section>
@endsection
