<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ResourceController extends Controller
{
    public function index(): View
    {
        return view('resources.index', [
            'posts' => collect(config('snapfinder_resources.posts'))->sortByDesc('published_at')->values(),
        ]);
    }

    public function show(string $slug): View
    {
        $posts = collect(config('snapfinder_resources.posts'));
        $post = $posts->firstWhere('slug', $slug);

        abort_unless($post, 404);

        return view('resources.show', [
            'post' => $post,
            'relatedPosts' => $posts->whereIn('slug', $post['related'])->values(),
        ]);
    }
}
