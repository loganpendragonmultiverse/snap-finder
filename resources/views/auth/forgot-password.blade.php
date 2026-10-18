@extends('layouts.app', ['title' => 'Forgot Password | Snap Finder'])

@section('content')
    <section class="relative overflow-hidden pt-32 pb-20 sm:pt-40 sm:pb-32">
        <div class="relative mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
            <section class="rounded-3xl border border-primary/20 bg-card p-6 sm:p-8">
                <p class="text-sm font-semibold uppercase tracking-wider text-primary">Password Reset</p>
                <h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Reset your password</h1>
                <p class="mt-4 text-muted-foreground">Enter your email and we will send a reset link.</p>
                <form method="POST" action="{{ route('password.email') }}" class="mt-8 grid gap-4">
                    @csrf
                    <label class="sf-label"><span>Email</span><input class="sf-field" type="email" name="email" value="{{ old('email') }}" required></label>
                    <label class="sf-label"><span>Bot check: {{ $captchaQuestion }}</span><input class="sf-field" type="text" name="captcha_answer" required></label>
                    <button class="inline-flex h-12 items-center justify-center rounded-xl bg-primary px-6 text-sm font-medium text-primary-foreground" type="submit">Send Reset Link</button>
                </form>
            </section>
        </div>
    </section>
@endsection
