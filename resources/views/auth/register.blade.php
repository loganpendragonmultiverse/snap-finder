@extends('layouts.app', ['title' => 'Create Account | Snap Finder'])

@section('content')
    <section class="relative overflow-hidden pt-32 pb-20 sm:pt-40 sm:pb-32">
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute top-1/4 left-1/2 h-[500px] w-[500px] -translate-x-1/2 rounded-full bg-primary/10 blur-[120px]"></div>
        </div>
        <div class="relative mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
            <section class="rounded-3xl border border-primary/20 bg-card p-6 sm:p-8">
                <p class="text-sm font-semibold uppercase tracking-wider text-primary">Join Snap Finder</p>
                <h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Create your Snap Finder listing account</h1>
                <p class="mt-4 text-muted-foreground">Make one account, submit your profile, and send it through moderation before it appears in the public directory.</p>
                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-destructive/30 bg-destructive/10 px-4 py-3 text-sm text-foreground">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif
                <form method="POST" action="{{ route('register.store') }}" class="mt-8 grid gap-4">
                    @csrf
                    <label class="sf-label"><span>Name</span><input class="sf-field" type="text" name="name" value="{{ old('name') }}" required>@error('name')<span class="text-sm text-destructive">{{ $message }}</span>@enderror</label>
                    <label class="sf-label"><span>Email</span><input class="sf-field" type="email" name="email" value="{{ old('email') }}" required>@error('email')<span class="text-sm text-destructive">{{ $message }}</span>@enderror</label>
                    <label class="sf-label"><span>Password</span><input class="sf-field" type="password" name="password" required>@error('password')<span class="text-sm text-destructive">{{ $message }}</span>@enderror</label>
                    <label class="sf-label"><span>Confirm password</span><input class="sf-field" type="password" name="password_confirmation" required>@error('password_confirmation')<span class="text-sm text-destructive">{{ $message }}</span>@enderror</label>
                    <label class="sf-label"><span>Bot check: {{ $captchaQuestion }}</span><input class="sf-field" type="text" name="captcha_answer" required>@error('captcha_answer')<span class="text-sm text-destructive">{{ $message }}</span>@enderror</label>
                    <button type="submit" class="mt-2 inline-flex h-12 items-center justify-center rounded-xl bg-primary px-6 text-sm font-medium text-primary-foreground transition-all hover:bg-primary/90">Create Listing Account</button>
                </form>
            </section>
        </div>
    </section>
@endsection
