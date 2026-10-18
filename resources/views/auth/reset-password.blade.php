@extends('layouts.app', ['title' => 'Reset Password | Snap Finder'])

@section('content')
    <section class="relative overflow-hidden pt-32 pb-20 sm:pt-40 sm:pb-32">
        <div class="relative mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
            <section class="rounded-3xl border border-primary/20 bg-card p-6 sm:p-8">
                <p class="text-sm font-semibold uppercase tracking-wider text-primary">Password Reset</p>
                <h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Choose a new password</h1>
                <form method="POST" action="{{ route('password.update') }}" class="mt-8 grid gap-4">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <label class="sf-label"><span>Email</span><input class="sf-field" type="email" name="email" value="{{ old('email', $email) }}" required></label>
                    <label class="sf-label"><span>Password</span><input class="sf-field" type="password" name="password" required></label>
                    <label class="sf-label"><span>Confirm password</span><input class="sf-field" type="password" name="password_confirmation" required></label>
                    <label class="sf-label"><span>Bot check: {{ $captchaQuestion }}</span><input class="sf-field" type="text" name="captcha_answer" required></label>
                    <button class="inline-flex h-12 items-center justify-center rounded-xl bg-primary px-6 text-sm font-medium text-primary-foreground" type="submit">Update Password</button>
                </form>
            </section>
        </div>
    </section>
@endsection
