@extends('layouts.app', [
    'title' => 'Snap Finder | Find Your Next Snapchat Connection',
    'description' => 'Find Snapchat friends, creators, gamers, and local connections through public listings organized by interest and intent.',
    'canonicalUrl' => route('welcome'),
])

@section('content')
    @php($homePosts = collect(config('snapfinder_resources.posts', []))->take(3))

    <section class="sf-home-hero" aria-labelledby="home-heading">
        <div class="sf-home-glow" aria-hidden="true"></div>
        <div class="sf-home-hero-inner">
            <p class="sf-kicker">Live directory</p>
            <h1 id="home-heading">Find your next <span>Snapchat connection</span></h1>
            <p class="sf-home-lead">Browse public listings for friends, creators, gamers, and local connections. Search by what you have in common, then read the profile before you add anyone.</p>

            <form class="sf-home-search" method="GET" action="{{ route('directory.index') }}" data-ga-event="search" data-ga-label="Homepage directory search">
                <label class="sf-home-search-field"><span class="sr-only">Search the directory</span><input type="search" name="q" placeholder="Search creators, gamers, friends..."></label>
                <button type="submit">Browse Directory <span aria-hidden="true">→</span></button>
            </form>

            <div class="sf-home-popular" aria-label="Popular directory categories">
                <span>Popular:</span>
                <a href="{{ route('directory.index', ['category' => 'friends']) }}">Friends</a>
                <a href="{{ route('directory.index', ['category' => 'gaming']) }}">Gaming</a>
                <a href="{{ route('directory.index', ['category' => 'creators']) }}">Creators</a>
                <a href="{{ route('directory.index', ['category' => 'lifestyle']) }}">Lifestyle</a>
                <a href="{{ route('directory.index', ['category' => 'local']) }}">Local</a>
            </div>
        </div>
    </section>

    <section class="sf-home-section" aria-labelledby="home-categories-heading">
        <div class="sf-home-section-heading">
            <p class="sf-kicker">Categories</p>
            <h2 id="home-categories-heading">Start with the kind of connection you want</h2>
            <p>The directory stays easier to use when every listing has a clear purpose.</p>
        </div>
        <div class="sf-home-category-grid">
            @foreach([
                ['friends', 'Friends', 'Conversation and shared interests'],
                ['gaming', 'Gaming', 'Teammates, communities, and creators'],
                ['creators', 'Creators', 'Audience and collaboration'],
                ['local', 'Local', 'General-area connections'],
                ['business', 'Business', 'Professional introductions'],
                ['lifestyle', 'Lifestyle', 'Hobbies, routines, and interests'],
            ] as [$slug, $name, $copy])
                <a href="{{ route('directory.index', ['category' => $slug]) }}"><strong>{{ $name }}</strong><span>{{ $copy }}</span></a>
            @endforeach
        </div>
    </section>

    <section id="how-it-works" class="sf-home-section sf-home-how" aria-labelledby="home-how-heading">
        <div class="sf-home-section-heading">
            <p class="sf-kicker">How it works</p>
            <h2 id="home-how-heading">Create, browse, connect</h2>
            <p>Listings provide context first. Snapchat handles remain visible to logged-in members, and adult areas require verified email plus 18+ access.</p>
        </div>
        <ol class="sf-home-steps">
            <li><span>01</span><strong>Create your account</strong><p>Register, verify your email, and describe what kind of connection you want.</p></li>
            <li><span>02</span><strong>Browse by intent</strong><p>Search approved listings by category, keyword, or general area.</p></li>
            <li><span>03</span><strong>Read the profile</strong><p>Check the bio, categories, and tags before deciding to connect.</p></li>
            <li><span>04</span><strong>Stay in control</strong><p>Pause your listing, update it, or report something that looks misleading.</p></li>
        </ol>
    </section>

    @if($homePosts->isNotEmpty())
        <section class="sf-home-section sf-home-blog" aria-labelledby="home-blog-heading">
            <div class="sf-home-section-heading sf-home-section-heading-row">
                <div><p class="sf-kicker">From the blog</p><h2 id="home-blog-heading">Before you send the first add</h2></div>
                <a class="sf-text-link" href="{{ route('blog.index') }}">View all articles →</a>
            </div>
            <div class="sf-home-blog-grid">
                @foreach($homePosts as $post)
                    <a href="{{ route('blog.show', $post['slug']) }}" class="sf-home-blog-card">
                        <span>{{ $post['category'] }}</span>
                        <h3>{{ $post['title'] }}</h3>
                        <p>{{ $post['excerpt'] }}</p>
                        <strong>Read article →</strong>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
@endsection
