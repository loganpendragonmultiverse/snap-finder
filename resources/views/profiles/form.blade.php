@extends('layouts.app', ['title' => ($profile ? 'Edit Listing' : 'Create Listing').' | Snap Finder'])

@section('content')
    <section class="relative overflow-hidden pt-32 pb-16 sm:pt-40 sm:pb-24">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <section class="rounded-3xl border border-primary/20 bg-card p-6 sm:p-8">
                <p class="text-sm font-semibold uppercase tracking-wider text-primary">Listing Studio</p>
                <h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">{{ $profile ? 'Edit listing' : 'Create listing' }}</h1>
                <p class="mt-4 text-muted-foreground">Verified users can publish listings immediately. Keep it clean, honest, and safe.</p>
                <form method="POST" action="{{ $profile ? route('profiles.update', $profile) : route('profiles.store') }}" class="mt-8 grid gap-4" enctype="multipart/form-data">
                    @csrf
                    @if($profile)
                        @method('PUT')
                    @endif
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="sf-label"><span>Display name</span><input class="sf-field" type="text" name="display_name" value="{{ old('display_name', $profile->display_name ?? '') }}" required></label>
                        <label class="sf-label"><span>Snapchat handle</span><input class="sf-field" type="text" name="snap_username" value="{{ old('snap_username', $profile->snap_username ?? '') }}" required></label>
                    </div>
                    <label class="sf-label"><span>Headline</span><input class="sf-field" type="text" name="headline" value="{{ old('headline', $profile->headline ?? '') }}" required></label>
                    <label class="sf-label"><span>Bio</span><textarea class="sf-field" name="bio" rows="5" required>{{ old('bio', $profile->bio ?? '') }}</textarea></label>
                    <label class="sf-label"><span>Looking for</span><input class="sf-field" type="text" name="looking_for" value="{{ old('looking_for', $profile->looking_for ?? '') }}" required></label>
                    <label class="sf-label"><span>Zip code</span><input class="sf-field" type="text" name="zip_code" value="{{ old('zip_code', $profile->zip_code ?? '') }}" inputmode="numeric" autocomplete="postal-code"></label>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="sf-label"><span>Profile image URL</span><input class="sf-field" type="url" name="profile_image" value="{{ old('profile_image', str_starts_with($profile->profile_image ?? '', 'https://') ? $profile->profile_image : '') }}" placeholder="https://example.com/photo.jpg"></label>
                        <label class="sf-label"><span>Or upload profile image</span><input class="sf-field" type="file" name="profile_image_upload" accept="image/png,image/jpeg,image/webp,image/gif"></label>
                    </div>
                    @if($profile?->profile_image)
                        <div class="flex items-center gap-4 rounded-2xl border border-border/50 bg-secondary/20 p-4">
                            <div class="sf-avatar-card">
                                <img src="{{ $profile->profile_image }}" alt="{{ $profile->display_name }}" class="sf-avatar-img" referrerpolicy="no-referrer">
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Current profile image. A new upload or secure image URL replaces it.</p>
                                <label class="mt-2 flex items-center gap-2 text-sm text-muted-foreground"><input type="checkbox" name="remove_profile_image" value="1"> Remove current image</label>
                            </div>
                        </div>
                    @endif

                    @unless($canSubmitNsfw)
                        <div class="rounded-2xl border border-primary/20 bg-primary/10 px-4 py-3 text-sm text-foreground">
                            NSFW categories unlock after your email is verified and age access is enabled.
                        </div>
                    @endunless

                    <fieldset class="rounded-2xl border border-border/50 bg-secondary/20 p-4">
                        <legend class="px-2 text-sm font-semibold text-primary">Categories</legend>
                        <p class="mt-2 text-sm text-muted-foreground">Pick at least one category. Listings cannot be uncategorized.</p>
                        @error('category_ids')<p class="mt-2 text-sm text-destructive">{{ $message }}</p>@enderror
                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                            @foreach($categories as $category)
                                <label class="flex items-center gap-3 text-sm text-muted-foreground">
                                    <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" @checked(in_array($category->id, old('category_ids', $profile?->categories->pluck('id')->all() ?? [])))>
                                    {{ $category->name }} @if($category->is_nsfw) (NSFW) @endif
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <fieldset class="rounded-2xl border border-border/50 bg-secondary/20 p-4">
                        <legend class="px-2 text-sm font-semibold text-primary">Tags</legend>
                        <p class="mt-3 text-sm text-muted-foreground">Add your own tags as a comma-separated list. Admin can review and clean up tags later.</p>
                        <label class="sf-label mt-4"><span>Tags, comma separated</span><input class="sf-field" type="text" name="new_tags" value="{{ old('new_tags', $profile?->tags->pluck('name')->implode(', ') ?? '') }}" placeholder="music, streaks, anime"></label>
                    </fieldset>
                    <label class="sf-label"><span>Bot check: {{ $captchaQuestion }}</span><input class="sf-field" type="text" name="captcha_answer" required></label>
                    <button class="inline-flex h-12 items-center justify-center rounded-xl bg-primary px-6 text-sm font-medium text-primary-foreground" type="submit">{{ $profile ? 'Update Listing' : 'Submit Listing' }}</button>
                </form>
            </section>
        </div>
    </section>
@endsection
