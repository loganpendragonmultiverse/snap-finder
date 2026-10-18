@extends('layouts.app', [
    'title' => $post['title'].' | Snap Finder',
    'description' => $post['description'],
    'canonicalUrl' => route('blog.show', $post['slug']),
    'ogType' => 'article',
    'articlePublishedTime' => $post['published_at'],
    'articleModifiedTime' => $post['updated_at'],
    'structuredData' => [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Article',
                'headline' => $post['title'],
                'description' => $post['description'],
                'datePublished' => $post['published_at'],
                'dateModified' => $post['updated_at'],
                'mainEntityOfPage' => route('blog.show', $post['slug']),
                'author' => ['@type' => 'Organization', 'name' => 'Snap Finder Editorial'],
                'publisher' => ['@type' => 'Organization', 'name' => 'Snap Finder', 'url' => route('welcome')],
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Snap Finder', 'item' => route('welcome')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => route('blog.index')],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $post['title'], 'item' => route('blog.show', $post['slug'])],
                ],
            ],
        ],
    ],
])

@section('content')
    <article class="sf-article">
        <header class="sf-article-hero">
            <a class="text-sm font-semibold uppercase tracking-wider text-primary hover:underline" href="{{ route('blog.index') }}">{{ $post['category'] }} article</a>
            <h1 class="mt-4 text-4xl font-bold tracking-tight sm:text-5xl">{{ $post['title'] }}</h1>
            <p class="mx-auto mt-6 max-w-3xl text-lg leading-relaxed text-muted-foreground">{{ $post['excerpt'] }}</p>
            <div class="mt-6 flex flex-wrap items-center justify-center gap-3 text-sm text-muted-foreground">
                <span>Snap Finder Editorial</span><span aria-hidden="true">•</span><time datetime="{{ $post['published_at'] }}">{{ date('F j, Y', strtotime($post['published_at'])) }}</time><span aria-hidden="true">•</span><span>{{ $post['reading_time'] }} min read</span>
            </div>
        </header>

        <div class="sf-article-body">
            <aside class="sf-article-note"><strong>Before you start</strong><span>Snap Finder is independent and is not affiliated with or endorsed by Snap Inc. A public listing is an introduction, not an identity or background check.</span></aside>

            @foreach($post['sections'] as $section)
                <section class="sf-article-section">
                    <h2>{{ $section['heading'] }}</h2>
                    @foreach($section['paragraphs'] as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                    @if(! empty($section['takeaways']))
                        <div class="sf-article-takeaways" aria-label="Useful details">
                            @foreach($section['takeaways'] as $takeaway)<p>{{ $takeaway }}</p>@endforeach
                        </div>
                    @endif
                </section>
            @endforeach

            <section class="mt-12 rounded-2xl border border-border/50 bg-card p-6 sm:p-8">
                <h2 class="text-2xl font-semibold">Put this guide into practice</h2>
                <p class="mt-3 leading-relaxed text-muted-foreground">{{ $post['cta']['copy'] }}</p>
                <a class="mt-6 inline-flex h-11 items-center justify-center rounded-xl bg-primary px-6 text-sm font-medium text-primary-foreground" href="{{ $post['cta']['url'] }}">{{ $post['cta']['label'] }}</a>
            </section>

            <section class="mt-12 border-t border-border/50 pt-10">
                <h2 class="text-2xl font-semibold">Related Snap Finder articles</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    @foreach($relatedPosts as $related)
                        <a class="rounded-xl border border-border/50 bg-card p-5 transition-colors hover:border-primary/40" href="{{ route('blog.show', $related['slug']) }}"><span class="text-xs font-semibold uppercase tracking-wider text-primary">{{ $related['category'] }}</span><span class="mt-2 block font-semibold">{{ $related['title'] }}</span></a>
                    @endforeach
                </div>
            </section>
        </div>
    </article>
@endsection
