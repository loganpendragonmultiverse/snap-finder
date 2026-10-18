@extends('layouts.app', ['title' => 'Account Settings | Snap Finder'])

@section('content')
    <section class="relative overflow-hidden pt-32 pb-20 sm:pt-40 sm:pb-32">
        <div class="relative mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
            <section class="rounded-3xl border border-primary/20 bg-card p-6 sm:p-8">
                <p class="text-sm font-semibold uppercase tracking-wider text-primary">Account</p>
                <h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">{{ $user->name }}</h1>
                <div class="mt-6 grid gap-3 text-sm text-muted-foreground">
                    <p>Email: {{ $user->email }}</p>
                    <p>Email status: {{ $user->hasVerifiedEmail() ? 'Verified' : 'Not verified' }}</p>
                    <p>NSFW access: {{ $user->hasNsfwAccess() ? 'Enabled' : 'Not enabled' }}</p>
                </div>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('account.export') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-border bg-background px-5 text-sm font-medium">Export My Data</a>
                </div>
                <form method="POST" action="{{ route('account.destroy') }}" class="mt-10 grid gap-4 rounded-2xl border border-red-500/30 bg-red-500/5 p-5" onsubmit="return confirm('Permanently delete your Snap Finder account and listings?');">
                    @csrf
                    @method('DELETE')
                    <h2 class="text-xl font-semibold">Delete account</h2>
                    <p class="text-sm text-muted-foreground">This permanently removes your account, listings, reports tied to your account, and saved listings.</p>
                    <label class="sf-label"><span>Current password</span><input class="sf-field" type="password" name="current_password" autocomplete="current-password" required></label>
                    <label class="sf-label"><span>Type DELETE</span><input class="sf-field" name="confirmation" autocomplete="off" required></label>
                    <button class="inline-flex h-11 items-center justify-center rounded-xl bg-red-600 px-5 text-sm font-medium text-white" type="submit">Delete My Account</button>
                </form>
            </section>
        </div>
    </section>
@endsection
