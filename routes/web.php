<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AgeGateController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\SnapProfileController;
use App\Models\SnapCategory;
use App\Models\SnapProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::bind('snapProfile', function ($value) {
    return SnapProfile::whereKey($value)->first() ?? abort(410);
});

Route::get('/robots.txt', function () {
    return response(
        "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /dashboard\nDisallow: /account\nDisallow: /login\nDisallow: /register\n\nSitemap: ".rtrim(config('app.url'), '/')."/sitemap.xml\n",
        200,
        ['Content-Type' => 'text/plain']
    );
});

Route::get('/sitemap.xml', function () {
    $baseUrl = rtrim(config('app.url'), '/').'';
    $contentLastModified = '2026-07-23T00:00:00+00:00';
    $urls = collect([
        ['loc' => "{$baseUrl}/", 'lastmod' => $contentLastModified, 'priority' => '1.0', 'changefreq' => 'weekly'],
        ['loc' => "{$baseUrl}/directory", 'lastmod' => $contentLastModified, 'priority' => '0.9', 'changefreq' => 'daily'],
        ['loc' => "{$baseUrl}/blog", 'lastmod' => $contentLastModified, 'priority' => '0.8', 'changefreq' => 'weekly'],
        ['loc' => "{$baseUrl}/contact", 'lastmod' => $contentLastModified, 'priority' => '0.5', 'changefreq' => 'monthly'],
        ['loc' => "{$baseUrl}/community-guidelines", 'lastmod' => $contentLastModified, 'priority' => '0.4', 'changefreq' => 'monthly'],
        ['loc' => "{$baseUrl}/privacy", 'lastmod' => $contentLastModified, 'priority' => '0.3', 'changefreq' => 'yearly'],
        ['loc' => "{$baseUrl}/terms", 'lastmod' => $contentLastModified, 'priority' => '0.3', 'changefreq' => 'yearly'],
    ]);

    $categoryUrls = SnapCategory::query()
        ->where('is_nsfw', false)
        ->orderBy('sort_order')
        ->get()
        ->map(fn (SnapCategory $category) => [
            'loc' => "{$baseUrl}/directory/category/{$category->slug}",
            'lastmod' => max(optional($category->updated_at)->toAtomString() ?? '', $contentLastModified),
            'priority' => '0.6',
            'changefreq' => 'weekly',
        ]);

    $profileUrls = SnapProfile::query()
        ->with(['categories', 'tags'])
        ->where('status', 'approved')
        ->whereNull('paused_at')
        ->where('is_adult', false)
        ->whereDoesntHave('categories', fn ($query) => $query->where('is_nsfw', true))
        ->whereDoesntHave('tags', fn ($query) => $query->where('is_nsfw', true))
        ->latest('updated_at')
        ->get()
        ->map(fn (SnapProfile $profile) => [
            'loc' => "{$baseUrl}/directory/{$profile->getRouteKey()}",
            'lastmod' => optional($profile->updated_at)->toAtomString(),
            'priority' => '0.7',
            'changefreq' => 'weekly',
        ]);

    $resourceUrls = collect(config('snapfinder_resources.posts', []))->map(fn (array $post) => [
        'loc' => "{$baseUrl}/blog/{$post['slug']}",
        'lastmod' => ($post['updated_at'] ?? $post['published_at']).'T00:00:00+00:00',
        'priority' => '0.7',
        'changefreq' => 'monthly',
    ]);

    $urls = $urls->merge($categoryUrls)->merge($resourceUrls)->merge($profileUrls);
    $xml = view('sitemap', ['urls' => $urls])->render();

    return response($xml, 200, ['Content-Type' => 'application/xml']);
})->name('sitemap');

Route::get('/', function (Request $request) {
    return view('welcome');
})->name('welcome');

Route::view('/terms', 'pages.terms')->name('terms');
Route::view('/privacy', 'pages.privacy')->name('privacy');
Route::view('/community-guidelines', 'pages.community-guidelines')->name('community-guidelines');
Route::get('/contact', [ContactController::class, 'create'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');
Route::get('/popular-categories', [SnapProfileController::class, 'popularCategories'])->middleware('throttle:30,1')->name('categories.popular');
Route::get('/blog', [ResourceController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [ResourceController::class, 'show'])->name('blog.show');
Route::permanentRedirect('/guides', '/blog');
Route::get('/guides/{slug}', fn (string $slug) => redirect()->route('blog.show', $slug, 301));
Route::get('/directory/category/{category}', [SnapProfileController::class, 'directoryCategory'])->middleware('throttle:80,1')->name('directory.category');
Route::get('/directory', [SnapProfileController::class, 'directory'])->middleware('throttle:80,1')->name('directory.index');
Route::get('/directory/{snapProfile}', [SnapProfileController::class, 'show'])->middleware('throttle:40,1')->name('directory.show');
Route::post('/directory/{snapProfile}/reports', [SnapProfileController::class, 'report'])->middleware('throttle:8,1')->name('directory.report');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:login')->name('login.store');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'signup'])->middleware('throttle:5,1')->name('register.store');
    Route::get('/forgot-password', [PasswordController::class, 'forgot'])->name('password.request');
    Route::post('/forgot-password', [PasswordController::class, 'sendResetLink'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password', [PasswordController::class, 'update'])->middleware('throttle:5,1')->name('password.update');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/email/verify', [AuthController::class, 'verificationNotice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verify'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', [AuthController::class, 'sendVerification'])->middleware('throttle:6,1')->name('verification.send');
    Route::get('/age-gate', [AgeGateController::class, 'create'])->name('age-gate.create');
    Route::post('/age-gate', [AgeGateController::class, 'store'])->middleware('throttle:age-gate')->name('age-gate.store');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/account', [DashboardController::class, 'settings'])->name('account.settings');
    Route::get('/account/export', [DashboardController::class, 'export'])->name('account.export');
    Route::delete('/account', [DashboardController::class, 'destroyAccount'])->name('account.destroy');
    Route::post('/directory/{snapProfile}/save', [SnapProfileController::class, 'toggleSave'])->middleware('throttle:30,1')->name('directory.save');

    Route::middleware('verified')->group(function () {
        Route::get('/profiles/create', [SnapProfileController::class, 'create'])->name('profiles.create');
        Route::post('/profiles', [SnapProfileController::class, 'store'])->middleware('throttle:5,60')->name('profiles.store');
        Route::get('/profiles/{snapProfile}/edit', [SnapProfileController::class, 'edit'])->name('profiles.edit');
        Route::put('/profiles/{snapProfile}', [SnapProfileController::class, 'update'])->middleware('throttle:10,60')->name('profiles.update');
        Route::put('/profiles/{snapProfile}/pause', [SnapProfileController::class, 'pause'])->name('profiles.pause');
        Route::delete('/profiles/{snapProfile}', [SnapProfileController::class, 'destroy'])->name('profiles.destroy');
    });
});

Route::middleware(['auth', 'active', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::put('/profiles/{snapProfile}', [AdminController::class, 'updateProfile'])->name('profiles.update');
    Route::delete('/profiles/{snapProfile}', [AdminController::class, 'deleteProfile'])->name('profiles.delete');
    Route::put('/reports/{report}', [AdminController::class, 'handleReport'])->name('reports.handle');
    Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{category}', [AdminController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}', [AdminController::class, 'deleteCategory'])->name('categories.delete');
    Route::put('/tags/{tag}', [AdminController::class, 'updateTag'])->name('tags.update');
    Route::delete('/tags/{tag}', [AdminController::class, 'deleteTag'])->name('tags.delete');
});
