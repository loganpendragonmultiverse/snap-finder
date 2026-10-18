@extends('layouts.app', ['title' => 'Verify Email | Snap Finder'])

@section('content')
    <section class="relative overflow-hidden pt-32 pb-20 sm:pt-40 sm:pb-32">
        <div class="relative mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
            <section class="rounded-3xl border border-primary/20 bg-card p-6 sm:p-8">
                <p class="text-sm font-semibold uppercase tracking-wider text-primary">Email Verification</p>
                <h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Verify your email before posting</h1>
                <p class="mt-4 text-muted-foreground">Check your inbox for the verification link. You can browse now, but listings require a verified email.</p>
                <p class="mt-3 text-sm text-muted-foreground">If the message is not in your inbox, check spam and resend it here. The link expires after 60 minutes.</p>
                @if (session('status'))
                    <div class="mt-6 rounded-2xl border border-primary/20 bg-primary/10 px-4 py-3 text-sm text-foreground">{{ session('status') }}</div>
                @endif
                @error('email')
                    <div class="mt-6 rounded-2xl border border-destructive/30 bg-destructive/10 px-4 py-3 text-sm text-foreground">{{ $message }}</div>
                @enderror
                <form method="POST" action="{{ route('verification.send') }}" class="mt-8">
                    @csrf
                    <button class="inline-flex h-12 items-center justify-center rounded-xl bg-primary px-6 text-sm font-medium text-primary-foreground" type="submit">Send Verification Email</button>
                </form>
            </section>
        </div>
    </section>
@endsection
