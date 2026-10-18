<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgeGateController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasNsfwAccess()) {
            return redirect()->route('dashboard')->with('status', 'NSFW access is already enabled.');
        }

        return view('age-gate', [
            'returnTo' => $this->safeReturnTo((string) $request->query('return', '')),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'date_of_birth' => ['required', 'date', 'before:today'],
            'confirm' => ['accepted'],
            'return_to' => ['nullable', 'string'],
        ]);

        $birthdate = Carbon::parse($data['date_of_birth']);
        if ($birthdate->age < 18) {
            return back()->withInput()->withErrors(['date_of_birth' => 'You must be 18 or older to access NSFW listings.']);
        }

        $request->user()->update([
            'age_verified_at' => now(),
            'age_verification_method' => 'self_attested_birthdate',
            'age_verification_meta' => ['provider' => null],
        ]);

        return redirect($this->safeReturnTo((string) ($data['return_to'] ?? '')))->with('status', 'Age gate complete.');
    }

    private function safeReturnTo(string $returnTo): string
    {
        $fallback = route('directory.index', ['nsfw' => 1]);
        if ($returnTo === '') {
            return $fallback;
        }

        $parts = parse_url($returnTo);
        if ($parts === false || isset($parts['host']) || isset($parts['scheme']) || ! str_starts_with($parts['path'] ?? '', '/')) {
            return $fallback;
        }

        return $returnTo;
    }
}
