<!DOCTYPE html>
<html lang="en" class="bg-background">
<head>
    @php
        $effectiveRobots = $robotsMeta
            ?? (request()->routeIs('login', 'register', 'password.*', 'verification.*')
                ? 'noindex, follow'
                : 'index, follow, max-image-preview:large');
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Snap Finder | Find Your Next Snapchat Connection' }}</title>
    <meta name="description" content="{{ $description ?? 'A curated Snapchat directory for discovering creators, friends, and gamers. Browse by category, scan profiles fast, connect with intent.' }}">
    <meta name="robots" content="{{ $effectiveRobots }}">
    <link rel="canonical" href="{{ $canonicalUrl ?? url()->current() }}">
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:site_name" content="Snap Finder">
    <meta property="og:title" content="{{ $title ?? 'Snap Finder | Find Your Next Snapchat Connection' }}">
    <meta property="og:description" content="{{ $description ?? 'A curated Snapchat directory for discovering creators, friends, and gamers. Browse by category, scan profiles fast, connect with intent.' }}">
    <meta property="og:url" content="{{ $canonicalUrl ?? url()->current() }}">
    @if(! empty($articlePublishedTime))
        <meta property="article:published_time" content="{{ $articlePublishedTime }}">
    @endif
    @if(! empty($articleModifiedTime))
        <meta property="article:modified_time" content="{{ $articleModifiedTime }}">
    @endif
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $title ?? 'Snap Finder | Find Your Next Snapchat Connection' }}">
    <meta name="twitter:description" content="{{ $description ?? 'A curated Snapchat directory for discovering creators, friends, and gamers. Browse by category, scan profiles fast, connect with intent.' }}">
    <script type="application/ld+json">{!! json_encode([
        chr(64).'context' => 'https://schema.org',
        '@type' => 'WebApplication',
        'name' => 'Snap Finder',
        'url' => config('app.url'),
        'applicationCategory' => 'SocialNetworkingApplication',
        'operatingSystem' => 'Web',
        'description' => 'A moderated Snapchat directory for discovering creators, friends, and gamers.',
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    @if(! empty($structuredData))
        <script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    @endif
    <link rel="preload" href="/_next/static/media/797e433ab948586e-s.p.0.q-h669a_dqa.woff2" as="font" crossorigin type="font/woff2">
    <link rel="preload" href="/_next/static/media/83afe278b6a6bb3c-s.p.0q-301v4kxxnr.woff2" as="font" crossorigin type="font/woff2">
    <link rel="stylesheet" href="/_next/static/chunks/0v50h61k8cx-1.css">
    <link rel="stylesheet" href="/_next/static/chunks/0v5l3z.1kvnze.css">
    <link rel="stylesheet" href="/app-shell.css?v=live-20260823-3">
    <link rel="icon" href="/icon.svg" type="image/svg+xml">



</head>
<body class="inter_5901b7c6-module__ec5Qua__variable geist_mono_d6617093-module__z61v7q__variable font-sans antialiased">
    <a href="#main-content" class="sf-skip-link">Skip to main content</a>
    <div class="min-h-screen bg-background">
        <header class="fixed left-0 right-0 top-0 z-50 border-b border-border/50 bg-background/80 backdrop-blur-xl">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <a href="{{ route('welcome') }}" class="flex items-center gap-2">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary">
                        @include('partials.ghost-icon', ['class' => 'h-5 w-5 text-primary-foreground'])
                    </div>
                    <span class="text-lg font-semibold tracking-tight">Snap Finder</span>
                </a>
                <nav class="sf-desktop-nav items-center gap-7" aria-label="Primary navigation">
                    <a href="{{ route('directory.index') }}" class="text-sm text-muted-foreground transition-colors hover:text-foreground">Directory</a>
                    <a href="{{ route('blog.index') }}" class="text-sm text-muted-foreground transition-colors hover:text-foreground">Blog</a>
                    <a href="{{ route('community-guidelines') }}" class="text-sm text-muted-foreground transition-colors hover:text-foreground">Guidelines</a>
                    <a href="/#how-it-works" class="text-sm text-muted-foreground transition-colors hover:text-foreground">How It Works</a>
                </nav>
                <div class="sf-desktop-actions items-center gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex h-8 items-center justify-center rounded-md px-3 text-sm font-medium text-muted-foreground transition-all hover:bg-accent hover:text-foreground">Dashboard</a>
                        @if (auth()->user()->is_admin)
                            <a href="{{ route('admin.index') }}" class="inline-flex h-8 items-center justify-center rounded-md px-3 text-sm font-medium text-muted-foreground transition-all hover:bg-accent hover:text-foreground">Admin</a>
                        @endif
                        <a href="{{ route('profiles.create') }}" class="inline-flex h-8 items-center justify-center rounded-md bg-primary px-3 text-sm font-medium text-primary-foreground transition-all hover:bg-primary/90">Create Listing</a>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium transition-all h-8 rounded-md gap-1.5 px-3 text-muted-foreground hover:text-foreground hover:bg-accent dark:hover:bg-accent/50">Log out</button></form>
                    @else
                        <a href="{{ route('directory.index') }}" class="inline-flex h-8 items-center justify-center rounded-md px-3 text-sm font-medium text-muted-foreground transition-all hover:bg-accent hover:text-foreground">Browse Directory</a>
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium transition-all h-8 rounded-md gap-1.5 px-3 text-muted-foreground hover:text-foreground hover:bg-accent dark:hover:bg-accent/50">Log in</a>
                        <a href="{{ route('register') }}" data-ga-event="sign_up_intent" data-ga-label="Header create listing" class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium transition-all h-8 rounded-md gap-1.5 px-3 bg-primary text-primary-foreground hover:bg-primary/90">Create Listing</a>
                    @endauth
                </div>
                <details class="sf-mobile-menu">
                    <summary class="inline-flex size-9 cursor-pointer list-none items-center justify-center rounded-md hover:bg-accent" aria-label="Open Snap Finder menu">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" aria-hidden="true"><path d="M4 6h16"></path><path d="M4 12h16"></path><path d="M4 18h16"></path></svg>
                    </summary>
                    <nav class="sf-mobile-menu-panel" aria-label="Mobile navigation">
                        <a href="{{ route('directory.index') }}">Directory</a><a href="{{ route('blog.index') }}">Blog</a><a href="{{ route('community-guidelines') }}">Guidelines</a><a href="/#how-it-works">How It Works</a>
                        @auth<a href="{{ route('dashboard') }}">Dashboard</a>@if(auth()->user()->is_admin)<a href="{{ route('admin.index') }}">Admin</a>@endif<a href="{{ route('profiles.create') }}">Create Listing</a><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Log out</button></form>
                        @else<a href="{{ route('login') }}">Log in</a><a href="{{ route('register') }}">Create Listing</a>@endauth
                    </nav>
                </details>
            </div>
        </header>

        <main id="main-content" tabindex="-1">
            @if (session('status'))
                <div class="mx-auto max-w-7xl px-4 pt-24 sm:px-6 lg:px-8">
                    <div
                        class="rounded-2xl border border-primary/20 bg-primary/10 px-4 py-3 text-sm text-foreground"
                        @if(session('ga_event')) data-ga-confirmed-event="{{ session('ga_event') }}" data-ga-label="{{ session('ga_label', request()->path()) }}" @endif
                    >{{ session('status') }}</div>
                </div>
            @endif

            @if ($errors->any() && ! request()->routeIs('login', 'login.store', 'register', 'register.store'))
                <div class="mx-auto max-w-7xl px-4 pt-24 sm:px-6 lg:px-8">
                    <div class="rounded-2xl border border-destructive/30 bg-destructive/10 px-4 py-3 text-sm text-foreground">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="border-t border-border/50 bg-card/50">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-8 sm:flex-row">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary">
                            @include('partials.ghost-icon', ['class' => 'h-4 w-4 text-primary-foreground'])
                        </div>
                        <span class="font-semibold">Snap Finder</span>
                    </div>
                    <nav class="flex flex-wrap items-center justify-center gap-6 text-sm">
                        <a href="{{ route('directory.index') }}" class="text-muted-foreground transition-colors hover:text-foreground">Directory</a>
                        <a href="{{ route('blog.index') }}" class="text-muted-foreground transition-colors hover:text-foreground">Blog</a>
                        @auth
                            <a href="{{ route('profiles.create') }}" class="text-muted-foreground transition-colors hover:text-foreground">Create Listing</a>
                            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="text-muted-foreground transition-colors hover:text-foreground">Log out</button></form>
                        @else
                            <a href="{{ route('login') }}" class="text-muted-foreground transition-colors hover:text-foreground">Log in</a>
                            <a href="{{ route('register') }}" class="text-muted-foreground transition-colors hover:text-foreground">Create Listing</a>
                        @endauth
                        <a href="{{ route('terms') }}" class="text-muted-foreground transition-colors hover:text-foreground">Terms</a>
                        <a href="{{ route('privacy') }}" class="text-muted-foreground transition-colors hover:text-foreground">Privacy</a>
                        <a href="{{ route('community-guidelines') }}" class="text-muted-foreground transition-colors hover:text-foreground">Guidelines</a>
                        <a href="{{ route('contact') }}" class="text-muted-foreground transition-colors hover:text-foreground">Contact</a>
                    </nav>
                    <p>Self-hosted open-source software</p>
                </div>
            </div>
        </footer>
    </div>
    <script src="/app-shell.js" defer></script>

</body>
</html>
