<?php

namespace App\Http\Controllers;

use App\Models\SnapProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        return view('dashboard', [
            'profiles' => SnapProfile::query()
                ->with(['categories', 'tags'])
                ->withCount('reports')
                ->where('user_id', $request->user()->id)
                ->latest()
                ->get(),
        ]);
    }

    public function settings(Request $request): View
    {
        return view('account.settings', ['user' => $request->user()]);
    }

    public function export(Request $request): JsonResponse
    {
        $user = $request->user()->load(['snapProfiles.categories', 'snapProfiles.tags']);

        return response()->json([
            'exported_at' => now()->toAtomString(),
            'account' => $user->only(['name', 'email', 'email_verified_at', 'created_at']),
            'listings' => $user->snapProfiles,
            'saved_listing_ids' => DB::table('saved_snap_profiles')->where('user_id', $user->id)->pluck('snap_profile_id'),
        ], 200, ['Content-Disposition' => 'attachment; filename="snap-finder-export.json"']);
    }

    public function destroyAccount(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'confirmation' => ['required', 'in:DELETE'],
        ]);
        $user = $request->user();
        if (! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $profileImages = $user->snapProfiles()->pluck('profile_image')->filter()->all();
        $user->newQuery()->whereKey($user->id)->delete();
        foreach ($profileImages as $profileImage) {
            $this->deleteOwnedProfileImage($profileImage);
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('welcome')->with('status', 'Your Snap Finder account and stored data were deleted.');
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
