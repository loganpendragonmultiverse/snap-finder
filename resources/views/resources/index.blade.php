@extends('layouts.app', [
    'title' => 'Snapchat Discovery Blog | Snap Finder',
    'description' => 'Practical guides for finding Snapchat friends, gamers, creators, and professional connections while protecting your privacy and safety.',
    'canonicalUrl' => route('blog.index'),
    'structuredData' => [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        'name' => 'Snap Finder Blog',
        'description' => 'Practical Snapchat discovery, networking, creator, and safety guides.',
        'url' => route('blog.index'),
        'mainEntity' => [
            '@type' => 'ItemList',
            'itemListElement' => $posts->map(fn ($post, $index) => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'url' => route('blog.show', $post['slug']),
                'name' => $post['title'],
            ])->all(),
        ],
    ],
])

@section('content')
    <section class="sf-blog-index relative overflow-hidden">
        <div class="pointer-events-none absolute inset-0"><div class="absolute left-1/2 top-1/4 h-[500px] w-[500px] -translate-x-1/2 rounded-full bg-primary/10 blur-[120px]"></div></div>
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <p class="text-sm font-semibold uppercase tracking-wider text-primary">Snap Finder Blog</p>
                <h1 class="mt-3 text-4xl font-bold tracking-tight sm:text-5xl">Practical answers for finding people on Snapchat</h1>
                <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-muted-foreground">Practical, safety-aware guides for finding people, building friendships, meeting gamers, connecting with creators, and recognizing risky behavior.</p>
            </div>

            <div class="sf-blog-index-grid">
                @foreach($posts as $post)
                    <article class="sf-blog-index-card">
                        <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-wider text-primary">
                            <span>{{ $post['category'] }}</span><span aria-hidden="true">•</span><time datetime="{{ $post['published_at'] }}">{{ date('F j, Y', strtotime($post['published_at'])) }}</time>
                        </div>
                        <h2 class="mt-4 text-2xl font-semibold tracking-tight"><a class="transition-colors hover:text-primary" href="{{ route('blog.show', $post['slug']) }}">{{ $post['title'] }}</a></h2>
                        <p class="mt-4 flex-1 leading-relaxed text-muted-foreground">{{ $post['excerpt'] }}</p>
                        <div class="mt-6 flex items-center justify-between gap-4 text-sm">
                            <span class="text-muted-foreground">{{ $post['reading_time'] }} min read</span>
                            <a class="font-medium text-primary hover:underline" href="{{ route('blog.show', $post['slug']) }}">Read article →</a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mx-auto mt-12 max-w-3xl rounded-2xl border border-primary/25 bg-primary/10 p-6 text-center sm:p-8">
                <h2 class="text-2xl font-semibold">Ready to browse?</h2>
                <p class="mt-3 text-muted-foreground">Put the guidance into practice with category-first discovery and public listing context.</p>
                <a href="{{ route('directory.index') }}" class="mt-6 inline-flex h-11 items-center justify-center rounded-xl bg-primary px-6 text-sm font-medium text-primary-foreground">Browse the directory</a>
            </div>
        </div>
    </section>
@endsection
