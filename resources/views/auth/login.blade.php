@extends('layouts.app', ['title' => 'Log In | Snap Finder'])

@section('content')
    <section class="relative overflow-hidden pt-32 pb-20 sm:pt-40 sm:pb-32">
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute top-1/4 left-1/2 h-[500px] w-[500px] -translate-x-1/2 rounded-full bg-primary/10 blur-[120px]"></div>
        </div>
        <div class="relative mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
            <section class="rounded-3xl border border-primary/20 bg-card p-6 sm:p-8">
                <p class="text-sm font-semibold uppercase tracking-wider text-primary">Member Access</p>
                <h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Log in to Snap Finder</h1>
                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-destructive/30 bg-destructive/10 px-4 py-3 text-sm text-foreground">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif
                @if (session('status'))
                    <div class="mt-6 rounded-2xl border border-primary/20 bg-primary/10 px-4 py-3 text-sm text-foreground">{{ session('status') }}</div>
                @endif
                <form method="POST" action="{{ route('login.store') }}" class="mt-8 grid gap-4" data-ga-event="login" data-ga-label="Snap Finder login form">
                    @csrf
                    <label class="sf-label"><span>Email</span><input class="sf-field" type="email" name="email" value="{{ old('email') }}" required>@error('email')<span class="text-sm text-destructive">{{ $message }}</span>@enderror</label>
                    <label class="sf-label"><span>Password</span><input class="sf-field" type="password" name="password" required>@error('password')<span class="text-sm text-destructive">{{ $message }}</span>@enderror</label>
                    <label class="sf-label"><span>Bot check: {{ $captchaQuestion }}</span><input class="sf-field" type="text" name="captcha_answer" required>@error('captcha_answer')<span class="text-sm text-destructive">{{ $message }}</span>@enderror</label>
                    <label class="flex items-center gap-3 text-sm text-muted-foreground">
                        <input type="checkbox" name="remember" value="1">
                        Keep me signed in
                    </label>
                    <button type="submit" class="mt-2 inline-flex h-12 items-center justify-center rounded-xl bg-primary px-6 text-sm font-medium text-primary-foreground transition-all hover:bg-primary/90">Log In</button>
                    <a href="{{ route('password.request') }}" class="text-sm text-primary hover:underline">Forgot password?</a>
                </form>
            </section>
        </div>
    </section>
@endsection
