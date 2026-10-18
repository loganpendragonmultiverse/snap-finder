@extends('layouts.app', ['title' => 'Age Gate | Snap Finder'])

@section('content')
    <section class="relative overflow-hidden pt-32 pb-20 sm:pt-40 sm:pb-32">
        <div class="relative mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
            <section class="rounded-3xl border border-primary/20 bg-card p-6 sm:p-8">
                <p class="text-sm font-semibold uppercase tracking-wider text-primary">NSFW Access</p>
                <h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Confirm you are 18+</h1>
                <p class="mt-4 text-muted-foreground">Enter your date of birth to confirm eligibility. Snap Finder stores only the resulting access status and verification method, not your full date of birth.</p>
                <form method="POST" action="{{ route('age-gate.store') }}" class="mt-8 grid gap-4" onsubmit="this.querySelector('button[type=submit]').disabled = true">
                    @csrf
                    <input type="hidden" name="return_to" value="{{ $returnTo }}">
                    <label class="sf-label"><span>Date of birth</span><input class="sf-field" type="date" name="date_of_birth" required></label>
                    <label class="flex items-center gap-3 text-sm text-muted-foreground"><input type="checkbox" name="confirm" value="1" required> I confirm I am at least 18 years old.</label>
                    <button class="inline-flex h-12 items-center justify-center rounded-xl bg-primary px-6 text-sm font-medium text-primary-foreground disabled:cursor-wait disabled:opacity-60" type="submit">Unlock NSFW Access</button>
                </form>
            </section>
        </div>
    </section>
@endsection
