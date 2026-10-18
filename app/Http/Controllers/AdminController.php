<?php

namespace App\Http\Controllers;

use App\Models\SnapCategory;
use App\Models\SnapProfile;
use App\Models\SnapProfileReport;
use App\Models\SnapTag;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $activeTab = request('tab', 'overview');
        $duplicateHandles = SnapProfile::query()
            ->select('snap_username', DB::raw('count(*) as total'), DB::raw('count(distinct user_id) as user_total'))
            ->groupBy('snap_username')
            ->having('user_total', '>', 1)
            ->orderByDesc('user_total')
            ->get();

        $duplicateProfiles = SnapProfile::query()
            ->with(['user', 'categories', 'tags'])
            ->withCount('reports')
            ->whereIn('snap_username', $duplicateHandles->pluck('snap_username'))
            ->orderBy('snap_username')
            ->latest()
            ->get()
            ->groupBy('snap_username');

        $highReportProfiles = SnapProfile::query()
            ->with(['user'])
            ->withCount('reports')
            ->having('reports_count', '>', 0)
            ->orderByDesc('reports_count')
            ->take(20)
            ->get();

        $suspiciousUsers = User::query()
            ->withCount([
                'snapProfiles',
                'snapProfiles as reported_profiles_count' => fn ($query) => $query->has('reports'),
            ])
            ->where(function ($query) {
                $query->where('is_suspended', true)
                    ->orWhereHas('snapProfiles.reports');
            })
            ->latest()
            ->take(25)
            ->get();

        return view('admin.index', [
            'totalUsers' => User::query()->count(),
            'totalProfiles' => SnapProfile::query()->count(),
            'users' => User::query()->withCount('snapProfiles')->latest()->paginate(10, ['*'], 'users_page')->withQueryString(),
            'profiles' => SnapProfile::query()->with(['user', 'categories', 'tags'])->withCount('reports')->latest()->paginate(10, ['*'], 'profiles_page')->withQueryString(),
            'approvedProfiles' => SnapProfile::query()->where('status', 'approved')->count(),
            'suspendedProfiles' => SnapProfile::query()->where('status', 'suspended')->count(),
            'reportedProfiles' => SnapProfile::query()->has('reports')->count(),
            'reports' => SnapProfileReport::query()->with(['profile.user', 'reporter'])->latest()->paginate(10, ['*'], 'reports_page')->withQueryString(),
            'categories' => SnapCategory::query()->orderBy('sort_order')->get(),
            'tags' => SnapTag::query()->orderBy('name')->get(),
            'duplicateHandles' => $duplicateHandles,
            'duplicateProfiles' => $duplicateProfiles,
            'highReportProfiles' => $highReportProfiles,
            'suspiciousUsers' => $suspiciousUsers,
            'activeTab' => $activeTab,
        ]);
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $user->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'is_admin' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'is_suspended' => ['nullable', 'boolean'],
        ]) + [
            'is_admin' => $request->boolean('is_admin'),
            'is_active' => $request->boolean('is_active'),
            'is_suspended' => $request->boolean('is_suspended'),
        ]);

        return back()->with('status', 'User moderation settings saved.');
    }

    public function updateProfile(Request $request, SnapProfile $snapProfile): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(SnapProfileController::STATUSES)],
            'is_featured' => ['nullable', 'boolean'],
            'is_adult' => ['nullable', 'boolean'],
            'status_reason' => ['nullable', 'string', 'max:1200'],
            'category_ids' => ['nullable', 'array', 'min:1'],
            'category_ids.*' => ['integer', Rule::exists('snap_categories', 'id')],
        ]);

        $snapProfile->update($data + [
            'is_approved' => $data['status'] === 'approved',
            'is_featured' => $request->boolean('is_featured'),
            'is_adult' => $request->boolean('is_adult'),
            'paused_at' => $data['status'] === 'suspended' ? now() : null,
        ]);

        if (array_key_exists('category_ids', $data)) {
            $snapProfile->categories()->sync($data['category_ids']);
        }

        if (! $snapProfile->categories()->exists()) {
            return back()->withErrors(['category_ids' => 'Every listing must belong to at least one category.']);
        }

        return back()->with('status', 'Listing moderation settings saved.');
    }

    public function deleteProfile(SnapProfile $snapProfile): RedirectResponse
    {
        $profileImage = $snapProfile->profile_image;
        $snapProfile->delete();
        $this->deleteOwnedProfileImage($profileImage);

        return back()->with('status', 'Listing deleted.');
    }

    public function handleReport(Request $request, SnapProfileReport $report): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['dismiss', 'approve', 'reject', 'suspend', 'mark_nsfw', 'delete_listing'])],
        ]);

        $profile = $report->profile;

        if ($data['action'] === 'dismiss' || ! $profile) {
            $report->delete();

            return back()->with('status', 'Report dismissed.');
        }

        if ($data['action'] === 'delete_listing') {
            $profileImage = $profile->profile_image;
            $profile->delete();
            $this->deleteOwnedProfileImage($profileImage);

            return back()->with('status', 'Report action applied.');
        }

        match ($data['action']) {
            'approve' => $profile->update([
                'status' => 'approved',
                'is_approved' => true,
                'status_reason' => 'Approved after report review.',
                'paused_at' => null,
            ]),
            'reject' => $profile->update([
                'status' => 'rejected',
                'is_approved' => false,
                'status_reason' => 'Rejected after report review.',
                'paused_at' => null,
            ]),
            'suspend' => $profile->update([
                'status' => 'suspended',
                'is_approved' => false,
                'status_reason' => 'Suspended after report review.',
                'paused_at' => now(),
            ]),
            'mark_nsfw' => $profile->update([
                'is_adult' => true,
                'status_reason' => 'Marked NSFW after report review.',
            ]),
        };

        return back()->with('status', 'Report action applied.');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_nsfw' => ['nullable', 'boolean'],
        ]);

        SnapCategory::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'sort_order' => $data['sort_order'] ?? 0,
            'is_nsfw' => $request->boolean('is_nsfw'),
        ]);

        return back()->with('status', 'Category created.');
    }

    public function updateCategory(Request $request, SnapCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_nsfw' => ['nullable', 'boolean'],
        ]);

        $category->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'sort_order' => $data['sort_order'] ?? 0,
            'is_nsfw' => $request->boolean('is_nsfw'),
        ]);

        return back()->with('status', 'Category updated.');
    }

    public function deleteCategory(SnapCategory $category): RedirectResponse
    {
        if ($category->profiles()->exists()) {
            return back()->withErrors(['category' => 'This category is attached to listings. Move those listings to another category before deleting it.']);
        }

        $category->delete();

        return back()->with('status', 'Category deleted.');
    }

    public function updateTag(Request $request, SnapTag $tag): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'is_nsfw' => ['nullable', 'boolean'],
        ]);

        $tag->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'is_nsfw' => $request->boolean('is_nsfw'),
        ]);

        return back()->with('status', 'Tag updated.');
    }

    public function deleteTag(SnapTag $tag): RedirectResponse
    {
        $tag->delete();

        return back()->with('status', 'Tag removed.');
    }

    private function deleteOwnedProfileImage(?string $profileImage): void
    {
        if (! $profileImage || ! preg_match('#^/uploads/profile-images/([a-f0-9-]+\.jpg)$#i', $profileImage, $matches)) {
            return;
        }

        $path = base_path('uploads/profile-images').DIRECTORY_SEPARATOR.$matches[1];
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
