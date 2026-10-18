<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BetaReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_safety_and_policy_surfaces_are_available(): void
    {
        foreach (['/', '/directory', '/blog', '/community-guidelines', '/contact', '/privacy', '/terms'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertHeader('X-Content-Type-Options', 'nosniff');
        }
    }

    public function test_blog_replaces_guides_without_losing_legacy_urls(): void
    {
        $this->get('/blog')
            ->assertOk()
            ->assertSee('Snap Finder Blog')
            ->assertSee('How to Make Friends on Snapchat Without Random Adds');

        $this->get('/blog/how-to-make-friends-on-snapchat')
            ->assertOk()
            ->assertSee('How to Make Friends on Snapchat Without Random Adds');

        $this->get('/guides')->assertRedirect('/blog');
        $this->get('/guides/how-to-make-friends-on-snapchat')
            ->assertRedirect(route('blog.show', 'how-to-make-friends-on-snapchat'));

        $this->get('/sitemap.xml')
            ->assertSee('/blog</loc>', false)
            ->assertSee('/blog/how-to-make-friends-on-snapchat</loc>', false)
            ->assertDontSee('/guides/');
    }

    public function test_shared_shell_keeps_the_dark_theme_and_exposes_the_same_primary_navigation(): void
    {
        foreach (['/', '/directory', '/blog', '/community-guidelines'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertSee('/app-shell.css?v=live-20260823-3', false)
                ->assertDontSee('/snap-finder.css', false)
                ->assertSee('>Directory</a>', false)
                ->assertSee('>Blog</a>', false)
                ->assertSee('>Guidelines</a>', false)
                ->assertSee('>How It Works</a>', false);
        }

        $this->get('/community-guidelines')
            ->assertSee('sf-guidelines-grid', false)
            ->assertSee('sf-rule-card-critical', false);
    }

    public function test_shared_shell_reflects_the_authenticated_user_on_every_public_surface(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        foreach (['/', '/directory', '/blog'] as $path) {
            $this->actingAs($user)->get($path)
                ->assertOk()
                ->assertSee('>Dashboard</a>', false)
                ->assertSee('>Create Listing</a>', false)
                ->assertSee('>Log out</button>', false)
                ->assertDontSee('>Log in</a>', false);
        }
    }

    public function test_blog_launch_contains_all_five_initial_articles(): void
    {
        $response = $this->get('/blog')->assertOk();

        foreach (config('snapfinder_resources.posts') as $post) {
            $response->assertSee($post['title']);
            $this->get('/blog/'.$post['slug'])
                ->assertOk()
                ->assertSee('sf-article-section', false)
                ->assertDontSee('list-disc', false);
        }

        $this->assertCount(5, config('snapfinder_resources.posts'));
    }

    public function test_sitemap_renders_valid_xml_instead_of_parsing_the_xml_declaration_as_php(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false)
            ->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false);
    }

    public function test_public_directory_bounds_search_input_and_explains_empty_filters(): void
    {
        $query = str_repeat('a', 200);

        $this->get('/directory?q='.$query)
            ->assertOk()
            ->assertViewHas('search', str_repeat('a', 80))
            ->assertSee('Nothing matched yet')
            ->assertSee('Clear all filters');
    }

    public function test_unknown_category_is_gone_instead_of_becoming_a_thin_index_page(): void
    {
        $this->get('/directory?category=does-not-exist')->assertGone();
    }

    public function test_private_account_and_listing_routes_stay_behind_authentication(): void
    {
        foreach (['/dashboard', '/account', '/profiles/create', '/account/export'] as $path) {
            $this->get($path)->assertRedirect(route('login'));
        }
    }
}
