<?php

namespace App\Http\Controllers;

use App\Models\SnapCategory;
use App\Models\SnapProfile;
use App\Models\SnapTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SnapProfileController extends Controller
{
    public const STATUSES = ['pending', 'approved', 'rejected', 'suspended'];

    protected array $blockedWords = [
        'minor', 'underage', 'teen sex', 'cp', 'child', 'childporn', 'lolita', 'jailbait',
        'scam', 'cashapp flip', 'fraud', 'stolen', 'impersonate', 'fake id',
    ];

    public static function categories(): array
    {
        return SnapCategory::query()->orderBy('sort_order')->pluck('name', 'slug')->all();
    }

    public function popularCategories(): JsonResponse
    {
        $defaults = collect([
            ['name' => 'Friends', 'slug' => 'friends', 'count' => 0],
            ['name' => 'Gaming', 'slug' => 'gaming', 'count' => 0],
            ['name' => 'Creators', 'slug' => 'creators', 'count' => 0],
            ['name' => 'Lifestyle', 'slug' => 'lifestyle', 'count' => 0],
            ['name' => 'Local', 'slug' => 'local', 'count' => 0],
            ['name' => 'Business', 'slug' => 'business', 'count' => 0],
            ['name' => 'Networking', 'slug' => 'networking', 'count' => 0],
            ['name' => 'Adult Creators', 'slug' => 'adult-creators', 'count' => 0, 'nsfw' => true],
            ['name' => 'After Dark', 'slug' => 'after-dark', 'count' => 0, 'nsfw' => true],
            ['name' => 'NSFW', 'slug' => 'nsfw', 'count' => 0, 'nsfw' => true],
        ]);

        $categories = SnapCategory::query()
            ->withCount(['profiles as approved_profiles_count' => function ($query) {
                $query->where('status', 'approved')->whereNull('paused_at');
            }])
            ->orderByDesc('approved_profiles_count')
            ->orderBy('sort_order')
            ->take(5)
            ->get()
            ->filter(fn ($category) => $category->approved_profiles_count > 0)
            ->map(fn ($category) => [
                'name' => $category->is_nsfw ? 'NSFW' : $category->name,
                'slug' => $category->slug,
                'count' => $category->approved_profiles_count,
                'nsfw' => $category->is_nsfw,
            ])
            ->values();

        return response()->json([
            'categories' => $categories->isNotEmpty() ? $categories : $defaults,
        ]);
    }

    public function directory(Request $request): View|RedirectResponse
    {
        $categorySlug = (string) $request->query('category', '');
        // Bound public filters before they reach several LIKE clauses. This keeps
        // accidental pasted text and abusive query strings from creating needless
        // database work while preserving normal discovery searches.
        $search = mb_substr(trim((string) $request->query('q', '')), 0, 80);
        $zipCode = mb_substr(trim((string) $request->query('zip', '')), 0, 10);
        $sort = in_array($request->query('sort'), ['newest', 'oldest', 'name'], true) ? $request->query('sort') : 'newest';
        $wantsNsfw = $request->boolean('nsfw') || SnapCategory::query()->where('slug', $categorySlug)->where('is_nsfw', true)->exists();

        $canAccessNsfw = $request->user()?->hasVerifiedEmail() && $request->user()?->hasNsfwAccess();
        $nsfwAccessMessage = null;

        if ($wantsNsfw && ! $canAccessNsfw) {
            $nsfwAccessMessage = 'You must be registered, logged in, email verified, and NSFW enabled to see listings in this category.';
        }

        $categories = SnapCategory::query()
            ->orderBy('sort_order')
            ->get();

        $activeCategoryModel = $categorySlug !== ''
            ? $categories->firstWhere('slug', $categorySlug)
            : null;

        if ($categorySlug !== '' && ! $activeCategoryModel && $categorySlug !== 'nsfw') {
            abort(410);
        }

        if ($categorySlug !== '' && $search === '' && $zipCode === '' && ! $wantsNsfw && ! $request->attributes->get('canonical_category_route')) {
            return redirect()->route('directory.category', ['category' => $categorySlug], 301);
        }

        $canonicalUrl = route('directory.index');
        $robotsMeta = null;

        if ($categorySlug !== '' && $search === '' && $zipCode === '' && ! $wantsNsfw) {
            $canonicalUrl = route('directory.category', ['category' => $categorySlug]);
        } elseif ($categorySlug !== '' || $search !== '' || $zipCode !== '' || $wantsNsfw) {
            $robotsMeta = 'noindex,follow,max-image-preview:large';
        }

        $seoContent = $search === '' && $zipCode === '' && ! $wantsNsfw
            ? $this->directorySeoContent($activeCategoryModel)
            : null;

        $profiles = $wantsNsfw && ! $canAccessNsfw
            ? new Collection
            : SnapProfile::query()
                ->with(['user', 'categories', 'tags'])
                ->withCount('reports')
                ->where(function ($visibility) use ($request) {
                    $visibility->where(function ($public) {
                        $public->where('status', 'approved')->whereNull('paused_at');
                    });

                    if ($request->user()) {
                        $visibility->orWhere('user_id', $request->user()->id);
                    }
                })
                ->when(! $canAccessNsfw, fn ($query) => $query->where('is_adult', false)->whereDoesntHave('categories', fn ($cat) => $cat->where('is_nsfw', true))->whereDoesntHave('tags', fn ($tag) => $tag->where('is_nsfw', true)))
                ->when($wantsNsfw, fn ($query) => $query->where(function ($inner) {
                    $inner->where('is_adult', true)
                        ->orWhereHas('categories', fn ($cat) => $cat->where('is_nsfw', true))
                        ->orWhereHas('tags', fn ($tag) => $tag->where('is_nsfw', true));
                }))
                ->when($categorySlug !== '', fn ($query) => $query->whereHas('categories', fn ($cat) => $cat->where('slug', $categorySlug)))
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($inner) use ($search) {
                        $inner->where('display_name', 'like', "%{$search}%")
                            ->orWhere('snap_username', 'like', "%{$search}%")
                            ->orWhere('headline', 'like', "%{$search}%")
                            ->orWhere('bio', 'like', "%{$search}%")
                            ->orWhere('looking_for', 'like', "%{$search}%")
                            ->orWhereHas('categories', fn ($cat) => $cat->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%"))
                            ->orWhereHas('tags', fn ($tag) => $tag->where('name', 'like', "%{$search}%"));
                    });
                })
                ->when($zipCode !== '', fn ($query) => $query->where('zip_code', $zipCode))
                ->orderByDesc('is_featured')
                ->when($sort === 'name', fn ($query) => $query->orderBy('display_name'))
                ->when($sort === 'oldest', fn ($query) => $query->oldest('updated_at'))
                ->when($sort === 'newest', fn ($query) => $query->latest('updated_at'))
                ->paginate(24)
                ->withQueryString();

        return view('directory.index', [
            'categories' => $categories,
            'activeCategory' => $categorySlug,
            'search' => $search,
            'zipCode' => $zipCode,
            'profiles' => $profiles,
            'showingNsfw' => $wantsNsfw,
            'nsfwAccessMessage' => $nsfwAccessMessage,
            'activeCategoryModel' => $activeCategoryModel,
            'canonicalUrl' => $canonicalUrl,
            'robotsMeta' => $robotsMeta,
            'seoContent' => $seoContent,
            'sort' => $sort,
            'starterPosts' => collect(config('snapfinder_resources.posts', []))
                ->sortByDesc('published_at')
                ->take(3)
                ->values(),
        ]);
    }

    protected function directorySeoContent(?SnapCategory $category): array
    {
        $content = [
            'directory' => [
                'title' => 'Find Snapchat Users by Interest | Snap Finder Directory',
                'description' => 'Browse moderated Snapchat profile listings by interest, category, keyword, or general area. Find friends, creators, gamers, and professional connections.',
                'heading' => 'Find Snapchat users by interest and intent',
                'intro' => [
                    'Snap Finder organizes public, user-submitted Snapchat listings around what people actually want to find. Browse focused categories for friends, gaming, creators, lifestyle interests, local connections, business, and professional networking instead of sorting through unrelated promotion posts.',
                    'Use keyword search when you already know an interest, browse a category when you want a narrower starting point, or add a ZIP code when location matters. Listing cards provide context before you decide whether to connect, while reports and administrator tools help address spam, impersonation, unsafe content, and inaccurate listings.',
                ],
                'tips' => [
                    'Choose one category that matches your reason for connecting.',
                    'Read the headline, bio, categories, and tags before adding anyone.',
                    'Use the report option when a listing looks misleading or unsafe.',
                ],
                'about' => 'Snapchat profile discovery directory',
            ],
            'friends' => [
                'title' => 'Find Snapchat Friends | Snap Finder Directory',
                'description' => 'Browse Snapchat friend listings from people looking for conversation, shared interests, and new social connections on Snap Finder.',
                'heading' => 'Find new Snapchat friends with shared interests',
                'intro' => [
                    'The Friends directory is for people who want conversation and genuine social connections on Snapchat. Browse public listings that explain what someone likes, what they want to talk about, and the kind of connection they are hoping to make.',
                    'Start with the profile headline and bio, then use tags to look for common interests such as music, art, fitness, travel, or everyday conversation. Snap Finder does not verify identities or guarantee compatibility, so move at your own pace, protect personal information, and report listings that appear misleading or unsafe.',
                ],
                'tips' => [
                    'Look for specific interests instead of adding accounts at random.',
                    'Keep early conversations on Snapchat and avoid sharing sensitive details.',
                    'Report spam, impersonation, harassment, or misleading listings.',
                ],
                'about' => 'Finding new Snapchat friends',
            ],
            'gaming' => [
                'title' => 'Find Snapchat Gamers | Gaming Directory | Snap Finder',
                'description' => 'Discover Snapchat gamers by game, platform, play style, and community interests in the Snap Finder gaming directory.',
                'heading' => 'Connect with Snapchat gamers and gaming communities',
                'intro' => [
                    'The Gaming directory helps players find Snapchat connections around games, platforms, teams, streams, clips, and gaming communities. Listings can describe favorite titles, competitive or casual play styles, and the kinds of gaming conversations or collaborations someone wants.',
                    'Search for a game, console, genre, or creator niche, then review the full listing before connecting. For safer discovery, keep account recovery information private, never share login codes, and be cautious with trades, paid offers, or links sent by people you have just met.',
                ],
                'tips' => [
                    'Search by game title, platform, genre, or play style.',
                    'Use profile context to find teammates, viewers, or fellow fans.',
                    'Treat account trades, giveaways, and payment requests cautiously.',
                ],
                'about' => 'Finding Snapchat gamers',
            ],
            'creators' => [
                'title' => 'Find Snapchat Creators | Creator Directory | Snap Finder',
                'description' => 'Browse Snapchat creators by niche, content style, and collaboration interests in the Snap Finder creator directory.',
                'heading' => 'Discover Snapchat creators by niche and style',
                'intro' => [
                    'The Creators directory brings together public listings from people making videos, art, music, photography, commentary, tutorials, and other original content for Snapchat audiences. Use profile details and tags to understand a creator\'s niche before deciding to follow or reach out.',
                    'Creators can use a focused listing to explain their work and the connections they want, including audience discovery, peer feedback, or collaboration. Snap Finder is not an endorsement or identity-verification service, so confirm partnership details independently and keep agreements clear before exchanging money, files, or account access.',
                ],
                'tips' => [
                    'Search for a niche, medium, topic, or collaboration type.',
                    'Review a creator\'s stated interests before pitching a partnership.',
                    'Never share passwords, verification codes, or unrestricted account access.',
                ],
                'about' => 'Discovering Snapchat creators',
            ],
            'lifestyle' => [
                'title' => 'Lifestyle Snapchat Accounts | Snap Finder Directory',
                'description' => 'Browse lifestyle Snapchat listings covering fitness, fashion, food, travel, wellness, hobbies, and everyday interests.',
                'heading' => 'Browse lifestyle Snapchat accounts by interest',
                'intro' => [
                    'The Lifestyle directory is a broad starting point for Snapchat accounts centered on daily interests such as fitness, fashion, food, travel, wellness, hobbies, routines, and personal projects. Profile headlines and tags help you narrow the directory to the subjects that fit your feed.',
                    'Lifestyle advice and recommendations are user-submitted and should not replace qualified medical, financial, or professional guidance. Review each listing on its own merits, be careful with products or services promoted by unfamiliar accounts, and use the report tools when content appears deceptive or unsafe.',
                ],
                'tips' => [
                    'Use a specific keyword such as fitness, fashion, travel, or food.',
                    'Check whether a listing clearly describes its focus and audience.',
                    'Independently evaluate health, financial, and product claims.',
                ],
                'about' => 'Discovering lifestyle Snapchat accounts',
            ],
            'local' => [
                'title' => 'Find Local Snapchat Users | Snap Finder Directory',
                'description' => 'Browse local Snapchat listings using a general ZIP-code area while keeping exact location details private.',
                'heading' => 'Find local Snapchat connections without posting an exact address',
                'intro' => [
                    'The Local directory helps people discover Snapchat listings connected to a general area. Search by ZIP code when nearby interests, events, communities, or collaboration matter. Public listing cards mask ZIP codes to a broad area rather than displaying a complete code.',
                    'A nearby listing is not proof of identity, background, or current location. Avoid sharing a home address or real-time location with someone you have just met. If you choose to meet offline, use a public place, tell someone you trust, and follow the safety guidance linked throughout Snap Finder.',
                ],
                'tips' => [
                    'Use location only when it is relevant to the connection you want.',
                    'Keep home, work, school, and real-time location details private.',
                    'Plan any offline meeting independently and use a public setting.',
                ],
                'about' => 'Finding local Snapchat users',
            ],
            'business' => [
                'title' => 'Snapchat Business Accounts | Snap Finder Directory',
                'description' => 'Discover Snapchat business accounts, independent brands, services, shops, and customer-facing profiles on Snap Finder.',
                'heading' => 'Discover Snapchat business accounts and independent brands',
                'intro' => [
                    'The Business directory is for public Snapchat listings tied to brands, shops, independent services, products, and customer-facing accounts. Search by industry, offering, or audience to find businesses whose Snapchat presence matches what you need.',
                    'A listing is not a business license, endorsement, or guarantee. Verify prices, policies, credentials, and contact details independently before buying, booking, or sharing payment information. Report listings that impersonate another company, make deceptive claims, or direct users toward unsafe transactions.',
                ],
                'tips' => [
                    'Search by service, product type, industry, or customer interest.',
                    'Confirm business details and payment terms outside the listing.',
                    'Use protected payment methods and avoid unusual payment requests.',
                ],
                'about' => 'Discovering Snapchat business accounts',
            ],
            'networking' => [
                'title' => 'Snapchat Networking Directory | Find Professional Connections',
                'description' => 'Browse Snapchat networking listings for collaborators, peers, founders, freelancers, and professional communities.',
                'heading' => 'Find collaborators and professional Snapchat connections',
                'intro' => [
                    'The Networking directory helps founders, freelancers, creators, professionals, and community builders describe the connections they want to make on Snapchat. Search by field, skill, project type, or collaboration goal to find more relevant conversations.',
                    'Treat every listing as an introduction rather than a credential check. Confirm experience, identity, ownership, and business terms before starting paid work or sharing private materials. Keep early outreach specific, explain why the connection is relevant, and report spam or deceptive opportunities.',
                ],
                'tips' => [
                    'Search by profession, skill, industry, or collaboration goal.',
                    'Open with a clear reason for connecting instead of a generic pitch.',
                    'Verify credentials and agreements before sharing work or payment.',
                ],
                'about' => 'Professional networking on Snapchat',
            ],
        ];

        return $content[$category?->slug ?? 'directory'] ?? $content['directory'];
    }

    public function directoryCategory(Request $request, string $category): View|RedirectResponse
    {
        if ($request->query()) {
            return redirect()->route('directory.index', ['category' => $category] + $request->query(), 301);
        }

        $request->query->set('category', $category);
        $request->attributes->set('canonical_category_route', true);

        return $this->directory($request);
    }

    public function show(Request $request, SnapProfile $snapProfile): View|RedirectResponse
    {
        $snapProfile->load(['user', 'categories', 'tags']);
        abort_unless($snapProfile->isPubliclyApproved(), 410);

        if ($snapProfile->isNsfw()) {
            if (! $request->user()) {
                return redirect()->route('login')->withErrors(['email' => 'Log in before viewing NSFW listings.']);
            }
            if (! $request->user()->hasVerifiedEmail()) {
                return redirect()->route('verification.notice')->withErrors(['email' => 'Verify your email before viewing NSFW listings.']);
            }
            if (! $request->user()->hasNsfwAccess()) {
                return redirect()->route('age-gate.create', ['return' => $request->fullUrl()]);
            }
        }

        $recent = collect($request->session()->get('recent_snap_profiles', []))
            ->reject(fn ($id) => (int) $id === $snapProfile->id)
            ->prepend($snapProfile->id)
            ->take(12)
            ->values()
            ->all();
        $request->session()->put('recent_snap_profiles', $recent);

        $isSaved = $request->user()
            ? DB::table('saved_snap_profiles')->where('user_id', $request->user()->id)->where('snap_profile_id', $snapProfile->id)->exists()
            : false;

        return view('directory.show', [
            'profile' => $snapProfile,
            'canViewHandle' => $request->user() && (! $snapProfile->isNsfw() || ($request->user()->hasVerifiedEmail() && $request->user()->hasNsfwAccess())),
            'reportCaptcha' => $this->refreshCaptcha('report_captcha'),
            'isSaved' => $isSaved,
        ]);
    }

    public function toggleSave(Request $request, SnapProfile $snapProfile): RedirectResponse
    {
        abort_unless($snapProfile->isPubliclyApproved(), 410);

        $query = DB::table('saved_snap_profiles')
            ->where('user_id', $request->user()->id)
            ->where('snap_profile_id', $snapProfile->id);

        if ($query->exists()) {
            $query->delete();

            return back()->with('status', 'Listing removed from saved listings.');
        }

        DB::table('saved_snap_profiles')->insert([
            'user_id' => $request->user()->id,
            'snap_profile_id' => $snapProfile->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('status', 'Listing saved.');
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        return view('profiles.form', $this->formData(null, $this->refreshCaptcha('listing_captcha')));
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $request->user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        abort_if($request->user()->snapProfiles()->count() >= 5 && ! $request->user()->is_admin, 403, 'New accounts are limited to five listings.');

        $data = $this->validated($request);
        if (! $this->validateCaptcha((string) $request->input('captcha_answer'), 'listing_captcha')) {
            return back()->withInput()->withErrors(['captcha_answer' => 'The bot check answer was incorrect.']);
        }
        if ($uploadedImage = $this->storeProfileImageUpload($request)) {
            $data['profile_image'] = $uploadedImage;
        }
        unset($data['profile_image_upload'], $data['remove_profile_image']);

        if ($this->containsNsfwSelection($data) && ! $request->user()->hasNsfwAccess()) {
            return redirect()->route('age-gate.create', ['return' => route('profiles.create')])->withErrors(['is_adult' => 'Unlock NSFW access before posting NSFW listings.']);
        }
        $data['is_adult'] = $this->containsNsfwSelection($data);

        $this->guardBlockedContent($data, $request);

        $profilePayload = collect($data)->except(['category_ids', 'new_tags', 'profile_image_upload'])->all();

        $profile = SnapProfile::create($profilePayload + [
            'user_id' => $request->user()->id,
            'category' => $this->legacyCategoryName($data),
            'status' => 'approved',
            'is_approved' => true,
            'is_featured' => false,
            'paused_at' => null,
        ]);

        $this->syncTaxonomy($profile, $data, $request);

        return redirect()->route('dashboard')
            ->with('status', 'Your listing is live.')
            ->with('ga_event', 'listing_created')
            ->with('ga_label', 'Snap Finder listing published');
    }

    public function edit(Request $request, SnapProfile $snapProfile): View
    {
        abort_unless($snapProfile->user_id === $request->user()->id, 404);

        return view('profiles.form', $this->formData($snapProfile->load(['categories', 'tags']), $this->refreshCaptcha('listing_captcha')));
    }

    public function update(Request $request, SnapProfile $snapProfile): RedirectResponse
    {
        abort_unless($snapProfile->user_id === $request->user()->id, 404);

        $data = $this->validated($request);
        if (! $this->validateCaptcha((string) $request->input('captcha_answer'), 'listing_captcha')) {
            return back()->withInput()->withErrors(['captcha_answer' => 'The bot check answer was incorrect.']);
        }

        $previousImage = $snapProfile->profile_image;
        $uploadedImage = $this->storeProfileImageUpload($request);
        if ($uploadedImage) {
            $data['profile_image'] = $uploadedImage;
        } elseif ($request->boolean('remove_profile_image')) {
            $data['profile_image'] = null;
        } elseif (blank($data['profile_image'] ?? null)) {
            $data['profile_image'] = $previousImage;
        }
        unset($data['profile_image_upload'], $data['remove_profile_image']);

        if ($this->containsNsfwSelection($data) && ! $request->user()->hasNsfwAccess()) {
            return redirect()->route('age-gate.create', ['return' => route('profiles.edit', $snapProfile)])->withErrors(['is_adult' => 'Unlock NSFW access before saving NSFW listings.']);
        }
        $data['is_adult'] = $this->containsNsfwSelection($data);

        $this->guardBlockedContent($data, $request);

        $profilePayload = collect($data)->except(['category_ids', 'new_tags', 'profile_image_upload'])->all();

        $snapProfile->update($profilePayload + [
            'category' => $this->legacyCategoryName($data),
            'status' => 'approved',
            'is_approved' => true,
            'is_featured' => false,
            'status_reason' => null,
        ]);

        $this->syncTaxonomy($snapProfile, $data, $request);

        if (($uploadedImage || $request->boolean('remove_profile_image')) && $previousImage !== $data['profile_image']) {
            $this->deleteOwnedProfileImage($previousImage);
        }

        return redirect()->route('dashboard')->with('status', 'Your listing was updated.');
    }

    public function pause(Request $request, SnapProfile $snapProfile): RedirectResponse
    {
        abort_unless($snapProfile->user_id === $request->user()->id, 404);

        $snapProfile->update(['paused_at' => $snapProfile->paused_at ? null : now()]);

        return back()->with('status', $snapProfile->paused_at ? 'Listing paused.' : 'Listing resumed.');
    }

    public function destroy(Request $request, SnapProfile $snapProfile): RedirectResponse
    {
        abort_unless($snapProfile->user_id === $request->user()->id, 404);

        $profileImage = $snapProfile->profile_image;
        $snapProfile->delete();
        $this->deleteOwnedProfileImage($profileImage);

        return redirect()->route('dashboard')->with('status', 'Listing deleted.');
    }

    public function report(Request $request, SnapProfile $snapProfile): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:120'],
            'details' => ['nullable', 'string', 'max:1200'],
            'captcha_answer' => ['required', 'string'],
        ]);

        if (! $this->validateCaptcha($data['captcha_answer'], 'report_captcha')) {
            return back()->withErrors(['captcha_answer' => 'The bot check answer was incorrect.']);
        }

        $snapProfile->reports()->create([
            'reporter_user_id' => $request->user()?->id,
            'reason' => $data['reason'],
            'details' => $data['details'] ?? null,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('status', 'Report submitted. Thank you.');
    }

    protected function formData(?SnapProfile $profile, string $captchaQuestion): array
    {
        $canSubmitNsfw = auth()->user()?->hasVerifiedEmail() && auth()->user()?->hasNsfwAccess();

        return [
            'profile' => $profile,
            'categories' => SnapCategory::query()
                ->when(! $canSubmitNsfw, fn ($query) => $query->where('is_nsfw', false))
                ->orderBy('sort_order')
                ->get(),
            'canSubmitNsfw' => $canSubmitNsfw,
            'captchaQuestion' => $captchaQuestion,
        ];
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'display_name' => ['required', 'string', 'max:120'],
            'snap_username' => ['required', 'string', 'max:120'],
            'headline' => ['required', 'string', 'max:180'],
            'bio' => ['required', 'string', 'max:1200'],
            'looking_for' => ['required', 'string', 'max:255'],
            'zip_code' => ['nullable', 'string', 'regex:/^\d{5}(?:-\d{4})?$/'],
            'profile_image' => [
                'nullable',
                'string',
                'max:500',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value !== '' && ! str_starts_with($value, 'https://') && ! preg_match('#^/uploads/profile-images/[a-f0-9-]+\.jpg$#i', $value)) {
                        $fail('The profile image must use a secure HTTPS URL.');
                    }
                },
            ],
            'profile_image_upload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'remove_profile_image' => ['nullable', 'boolean'],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', Rule::exists('snap_categories', 'id')],
            'new_tags' => ['nullable', 'string', 'max:255'],
        ]) + [
            'is_adult' => false,
            'location' => null,
        ];
    }

    protected function storeProfileImageUpload(Request $request): ?string
    {
        if (! $request->hasFile('profile_image_upload')) {
            return null;
        }

        $file = $request->file('profile_image_upload');
        $contents = file_get_contents($file->getRealPath());
        $image = $contents !== false ? @imagecreatefromstring($contents) : false;
        abort_unless($image !== false, 422, 'The uploaded image could not be decoded safely.');

        $fileName = (string) Str::uuid().'.jpg';
        $directory = base_path('uploads/profile-images');

        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $target = $directory.DIRECTORY_SEPARATOR.$fileName;
        imageinterlace($image, true);
        $saved = imagejpeg($image, $target, 88);
        imagedestroy($image);
        abort_unless($saved, 500, 'The uploaded image could not be stored.');

        return '/uploads/profile-images/'.$fileName;
    }

    protected function deleteOwnedProfileImage(?string $profileImage): void
    {
        if (! $profileImage || ! preg_match('#^/uploads/profile-images/([a-f0-9-]+\.jpg)$#i', $profileImage, $matches)) {
            return;
        }

        $path = base_path('uploads/profile-images').DIRECTORY_SEPARATOR.$matches[1];
        if (is_file($path)) {
            @unlink($path);
        }
    }

    protected function syncTaxonomy(SnapProfile $profile, array $data, Request $request): void
    {
        $profile->categories()->sync($data['category_ids']);

        $newTags = collect(explode(',', (string) $request->input('new_tags')))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->take(8)
            ->map(function ($name) use ($data) {
                $slug = Str::slug(Str::limit($name, 40, ''));
                if ($slug === '') {
                    return null;
                }

                return SnapTag::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => Str::headline(Str::limit($name, 40, '')), 'is_nsfw' => (bool) ($data['is_adult'] ?? false)]
                )->id;
            })
            ->filter();

        $profile->tags()->sync($newTags->unique()->values());
    }

    protected function legacyCategoryName(array $data): string
    {
        $firstCategoryId = collect($data['category_ids'] ?? [])->filter()->first();

        return SnapCategory::query()->where('id', $firstCategoryId)->value('name') ?? 'General';
    }

    protected function containsNsfwSelection(array $data): bool
    {
        $categoryIds = collect($data['category_ids'] ?? [])->filter()->all();

        return $categoryIds && SnapCategory::query()->whereIn('id', $categoryIds)->where('is_nsfw', true)->exists();
    }

    protected function guardBlockedContent(array $data, Request $request): void
    {
        $content = strtolower(implode(' ', [
            $data['display_name'] ?? '',
            $data['snap_username'] ?? '',
            $data['headline'] ?? '',
            $data['bio'] ?? '',
            $data['looking_for'] ?? '',
            $data['new_tags'] ?? '',
        ]));

        foreach ($this->blockedWords as $word) {
            if (str_contains($content, $word)) {
                abort(422, 'This listing contains prohibited or unsafe language and cannot be submitted.');
            }
        }
    }
}
