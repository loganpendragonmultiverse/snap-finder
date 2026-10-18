@extends('layouts.app', [
    'title' => 'Contact | Snap Finder',
    'description' => 'Contact Snap Finder about profile listings, account help, safety reports, policy questions, or product feedback.',
])

@section('content')
    <section class="relative overflow-hidden pt-32 pb-16 sm:pt-40 sm:pb-24">
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute top-1/4 left-1/2 h-[500px] w-[500px] -translate-x-1/2 rounded-full bg-primary/10 blur-[120px]"></div>
        </div>
        <div class="relative mx-auto grid max-w-6xl gap-8 px-4 sm:px-6 lg:grid-cols-[.85fr_1.15fr] lg:px-8">
            <div>
                <h2 class="text-sm font-semibold uppercase tracking-wider text-primary">Contact</h2>
                <h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Contact Snap Finder</h1>
                <p class="mt-4 text-lg text-muted-foreground">Use this for feedback, listing questions, safety reports, account issues, or policy questions.</p>
                <div class="mt-8 rounded-3xl border border-primary/20 bg-card p-6">
                    <p class="text-sm text-muted-foreground">Prefer email?</p>
                    Contact this instance operator using this form.
                </div>
            </div>

            @if (session('contact_submitted'))
                <div class="flex min-h-80 flex-col items-center justify-center rounded-3xl border border-primary/30 bg-primary/10 p-8 text-center" role="status" data-ga-confirmed-event="generate_lead" data-ga-label="Snap Finder confirmed contact submission">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-primary text-2xl font-bold text-primary-foreground" aria-hidden="true">✓</div>
                    <h2 class="mt-5 text-2xl font-semibold">Message received</h2>
                    <p class="mt-3 max-w-md text-muted-foreground">Thanks for reaching out. The form is now closed for this submission to help prevent accidental duplicates and spam.</p>
                    <a href="{{ route('welcome') }}" class="mt-6 inline-flex h-11 items-center justify-center rounded-xl border border-border bg-background px-5 text-sm font-medium transition-all hover:bg-secondary">Return home</a>
                </div>
            @else
                <form method="POST" action="{{ route('contact.store') }}" class="grid gap-4 rounded-3xl border border-primary/20 bg-card p-6 sm:p-8">
                    @csrf
                    <label class="sf-label"><span>Name</span><input class="sf-field" name="name" value="{{ old('name') }}" autocomplete="name" required></label>
                    <label class="sf-label"><span>Email</span><input class="sf-field" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required></label>
                    <label class="sf-label"><span>Subject</span><input class="sf-field" name="subject" value="{{ old('subject') }}" required></label>
                    <label class="sf-label"><span>Message</span><textarea class="sf-field" name="message" rows="7" required>{{ old('message') }}</textarea></label>
                    <label aria-hidden="true" style="position:absolute;left:-10000px;width:1px;height:1px;overflow:hidden"><span>Address</span><input name="address" tabindex="-1" autocomplete="off"></label>
                    <label class="sf-label"><span>Bot check: {{ $captchaQuestion }}</span><input class="sf-field" name="captcha_answer" inputmode="numeric" autocomplete="off" required></label>
                    <button class="inline-flex h-12 items-center justify-center rounded-xl bg-primary px-6 text-sm font-medium text-primary-foreground transition-all hover:bg-primary/90" type="submit">Send Message</button>
                </form>
            @endif
        </div>
    </section>
@endsection
