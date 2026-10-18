@extends('layouts.app', ['title' => 'Admin | Snap Finder'])

@php
    $tabs = [
        'overview' => 'Overview',
        'users' => 'Users',
        'listings' => 'Listings',
        'reports' => 'Reports',
        'signals' => 'Spam Signals',
        'taxonomy' => 'Categories & Tags',
    ];
    $statusClass = fn ($status) => match ($status) {
        'approved' => 'border-primary/30 bg-primary/10 text-primary',
        'pending' => 'border-yellow-400/40 bg-yellow-400/10 text-yellow-200',
        'suspended', 'rejected' => 'border-destructive/40 bg-destructive/10 text-destructive',
        default => 'border-border bg-card text-muted-foreground',
    };
@endphp

@section('content')
    <section class="relative overflow-hidden pt-32 pb-16 sm:pt-40 sm:pb-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-primary">Admin Console</h2>
                    <p class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Moderate Snap Finder</p>
                    <p class="mt-4 max-w-2xl text-lg text-muted-foreground">Review users, listings, reports, duplicate handles, and taxonomy without digging through one oversized page.</p>
                </div>
                <a href="{{ route('directory.index') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-border bg-background px-5 text-sm font-medium transition-all hover:bg-secondary">View directory</a>
            </div>

            <nav class="mt-8 flex gap-2 overflow-x-auto pb-2">
                @foreach ($tabs as $tab => $label)
                    <a href="{{ route('admin.index', ['tab' => $tab]) }}" class="inline-flex h-10 shrink-0 items-center justify-center rounded-xl border px-4 text-sm font-medium transition-all {{ $activeTab === $tab ? 'border-primary/50 bg-primary/10 text-primary' : 'border-border bg-card text-muted-foreground hover:text-foreground' }}">{{ $label }}</a>
                @endforeach
            </nav>

            <div class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <div class="rounded-xl border border-primary/20 bg-card px-4 py-3">
                    <p class="text-xs uppercase tracking-wide text-muted-foreground">Total users</p>
                    <p class="mt-1 text-2xl font-bold">{{ $totalUsers }}</p>
                </div>
                <div class="rounded-xl border border-primary/20 bg-card px-4 py-3">
                    <p class="text-xs uppercase tracking-wide text-muted-foreground">Total listings</p>
                    <p class="mt-1 text-2xl font-bold">{{ $totalProfiles }}</p>
                </div>
                <div class="rounded-xl border border-primary/20 bg-card px-4 py-3">
                    <p class="text-xs uppercase tracking-wide text-muted-foreground">Live</p>
                    <p class="mt-1 text-2xl font-bold">{{ $approvedProfiles }}</p>
                </div>
                <div class="rounded-xl border border-primary/20 bg-card px-4 py-3">
                    <p class="text-xs uppercase tracking-wide text-muted-foreground">Reported</p>
                    <p class="mt-1 text-2xl font-bold">{{ $reportedProfiles }}</p>
                </div>
            </div>

            @if ($activeTab === 'overview')
                <div class="mt-8 grid gap-8 lg:grid-cols-3">
                    <section class="rounded-3xl border border-primary/20 bg-card p-6 lg:col-span-2">
                        <h3 class="text-xl font-semibold">Live activity</h3>
                        <div class="mt-6 grid gap-4 md:grid-cols-3">
                            <div class="rounded-2xl border border-border/50 bg-secondary/20 p-4">
                                <p class="text-sm text-muted-foreground">Live listings</p>
                                <p class="mt-2 text-2xl font-bold">{{ $approvedProfiles }}</p>
                            </div>
                            <div class="rounded-2xl border border-border/50 bg-secondary/20 p-4">
                                <p class="text-sm text-muted-foreground">Suspended listings</p>
                                <p class="mt-2 text-2xl font-bold">{{ $suspendedProfiles }}</p>
                            </div>
                            <div class="rounded-2xl border border-border/50 bg-secondary/20 p-4">
                                <p class="text-sm text-muted-foreground">Cross-account duplicates</p>
                                <p class="mt-2 text-2xl font-bold">{{ $duplicateHandles->count() }}</p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-primary/20 bg-card p-6">
                        <h3 class="text-xl font-semibold">Quick signals</h3>
                        <div class="mt-5 grid gap-3 text-sm text-muted-foreground">
                            <a class="rounded-2xl border border-border/50 bg-secondary/20 p-4 hover:text-foreground" href="{{ route('admin.index', ['tab' => 'reports']) }}">{{ $reportedProfiles }} listing(s) with reports</a>
                            <a class="rounded-2xl border border-border/50 bg-secondary/20 p-4 hover:text-foreground" href="{{ route('admin.index', ['tab' => 'signals']) }}">{{ $duplicateHandles->count() }} duplicate handle group(s)</a>
                        </div>
                    </section>
                </div>
            @endif

            @if ($activeTab === 'users')
                <section class="mt-8 rounded-3xl border border-primary/20 bg-card p-6">
                    <h3 class="text-xl font-semibold">Users</h3>
                    <div class="mt-6 grid gap-3">
                        @foreach ($users as $user)
                            <details class="rounded-2xl border border-border/50 bg-secondary/20 p-4">
                                <summary class="cursor-pointer list-none">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <p class="font-semibold">{{ $user->name }}</p>
                                            <p class="text-sm text-muted-foreground">{{ $user->email }} | {{ $user->snap_profiles_count }} listing(s)</p>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            @if ($user->hasVerifiedEmail())
                                                <span class="rounded-full border border-primary/30 bg-primary/10 px-3 py-1 text-xs text-primary">Verified</span>
                                            @else
                                                <span class="rounded-full border border-border bg-card px-3 py-1 text-xs text-muted-foreground">Unverified</span>
                                            @endif
                                            @if ($user->is_admin)
                                                <span class="rounded-full border border-primary/30 bg-primary/10 px-3 py-1 text-xs text-primary">Admin</span>
                                            @endif
                                            @if ($user->is_suspended)
                                                <span class="rounded-full border border-destructive/40 bg-destructive/10 px-3 py-1 text-xs text-destructive">Suspended</span>
                                            @endif
                                            <span class="rounded-full border border-border bg-card px-3 py-1 text-xs text-muted-foreground">Manage</span>
                                        </div>
                                    </div>
                                </summary>
                                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="mt-4 grid gap-3">
                                    @csrf
                                    @method('PUT')
                                    <label class="sf-label"><span>Name</span><input class="sf-field" type="text" name="name" value="{{ $user->name }}" required></label>
                                    <label class="sf-label"><span>Email</span><input class="sf-field" type="email" name="email" value="{{ $user->email }}" required></label>
                                    <div class="flex flex-wrap gap-5 text-sm text-muted-foreground">
                                        <label class="flex items-center gap-3"><input type="checkbox" name="is_admin" value="1" @checked($user->is_admin)> Admin</label>
                                        <label class="flex items-center gap-3"><input type="checkbox" name="is_active" value="1" @checked($user->is_active)> Active</label>
                                        <label class="flex items-center gap-3"><input type="checkbox" name="is_suspended" value="1" @checked($user->is_suspended)> Suspended</label>
                                    </div>
                                    <button type="submit" class="inline-flex h-10 items-center justify-center rounded-xl bg-primary px-4 text-sm font-medium text-primary-foreground transition-all hover:bg-primary/90">Save user</button>
                                </form>
                            </details>
                        @endforeach
                    </div>
                    <div class="sf-pagination mt-6">{{ $users->links() }}</div>
                </section>
            @endif

            @if ($activeTab === 'listings')
                <section class="mt-8 rounded-3xl border border-primary/20 bg-card p-6">
                    <h3 class="text-xl font-semibold">All listings</h3>
                    <div class="mt-6 grid gap-4">
                        @forelse ($profiles as $profile)
                            @include('admin.partials.profile-card', ['profile' => $profile, 'statusClass' => $statusClass])
                        @empty
                            <p class="text-sm text-muted-foreground">No listings in this section.</p>
                        @endforelse
                    </div>
                    <div class="sf-pagination mt-6">{{ $profiles->links() }}</div>
                </section>
            @endif

            @if ($activeTab === 'reports')
                <section class="mt-8 rounded-3xl border border-primary/20 bg-card p-6">
                    <h3 class="text-xl font-semibold">Reported listings</h3>
                    <div class="mt-6 grid gap-4 lg:grid-cols-2">
                        @forelse ($reports as $report)
                            <article class="rounded-2xl border border-border/50 bg-secondary/20 p-4">
                                <p class="font-semibold">{{ $report->reason }}</p>
                                <p class="mt-1 text-sm text-muted-foreground">{{ $report->profile?->display_name ?? 'Deleted listing' }} | {{ $report->reporter?->email ?? 'Guest report' }}</p>
                                @if ($report->details)
                                    <p class="mt-3 text-sm text-muted-foreground">{{ $report->details }}</p>
                                @endif
                                <p class="mt-3 text-xs text-muted-foreground">{{ $report->created_at?->format('M j, Y g:i A') }}</p>
                                <form method="POST" action="{{ route('admin.reports.handle', $report) }}" class="mt-4 grid gap-3">
                                    @csrf
                                    @method('PUT')
                                    <label class="sf-label">
                                        <span>Action</span>
                                        <select class="sf-field" name="action" required>
                                            <option value="dismiss">Dismiss report</option>
                                            @if ($report->profile)
                                                <option value="approve">Approve listing</option>
                                                <option value="reject">Reject listing</option>
                                                <option value="suspend">Suspend listing</option>
                                                <option value="mark_nsfw">Mark listing NSFW</option>
                                                <option value="delete_listing">Delete listing</option>
                                            @endif
                                        </select>
                                    </label>
                                    <button type="submit" class="inline-flex h-10 items-center justify-center rounded-xl bg-primary px-4 text-sm font-medium text-primary-foreground transition-all hover:bg-primary/90">Apply report action</button>
                                </form>
                            </article>
                        @empty
                            <p class="text-sm text-muted-foreground">No reports submitted.</p>
                        @endforelse
                    </div>
                    <div class="sf-pagination mt-6">{{ $reports->links() }}</div>
                </section>
            @endif

            @if ($activeTab === 'signals')
                <div class="mt-8 grid gap-8 xl:grid-cols-2">
                    <section class="rounded-3xl border border-primary/20 bg-card p-6">
                        <h3 class="text-xl font-semibold">Duplicate Snapchat handles</h3>
                        <p class="mt-2 text-sm text-muted-foreground">Only handles submitted by more than one user account are flagged here. One user may list the same handle in multiple categories.</p>
                        <div class="mt-6 grid gap-3">
                            @forelse ($duplicateHandles as $handle)
                                <div class="rounded-2xl border border-destructive/40 bg-destructive/10 p-4">
                                    <p class="font-semibold">{{ '@'.$handle->snap_username }}</p>
                                    <p class="mt-1 text-sm text-muted-foreground">{{ $handle->total }} listing(s) across {{ $handle->user_total }} account(s)</p>
                                    <div class="mt-4 grid gap-3">
                                        @foreach(($duplicateProfiles[$handle->snap_username] ?? collect()) as $profile)
                                            <div class="rounded-xl border border-border/50 bg-card/60 p-3">
                                                <p class="text-sm font-semibold">{{ $profile->display_name }} <span class="text-muted-foreground">by {{ $profile->user?->email }}</span></p>
                                                <p class="mt-1 text-xs text-muted-foreground">{{ $profile->reports_count }} report(s) | {{ $profile->status }} | {{ $profile->categories->pluck('name')->join(', ') ?: 'Uncategorized' }}</p>
                                                @include('admin.partials.signal-actions', ['profile' => $profile])
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-muted-foreground">No cross-account duplicate handles found.</p>
                            @endforelse
                        </div>
                    </section>

                    <section class="rounded-3xl border border-primary/20 bg-card p-6">
                        <h3 class="text-xl font-semibold">Reported and suspicious activity</h3>
                        <div class="mt-6 grid gap-3">
                            @forelse ($highReportProfiles as $profile)
                                <div class="rounded-2xl border border-border/50 bg-secondary/20 p-4">
                                    <p class="font-semibold">{{ $profile->display_name }} <span class="text-muted-foreground">{{ '@'.$profile->snap_username }}</span></p>
                                    <p class="mt-1 text-sm text-muted-foreground">{{ $profile->reports_count }} report(s) | {{ $profile->user?->email }}</p>
                                    @include('admin.partials.signal-actions', ['profile' => $profile])
                                </div>
                            @empty
                                <p class="text-sm text-muted-foreground">No listings have reports yet.</p>
                            @endforelse
                        </div>
                    </section>

                    <section class="rounded-3xl border border-primary/20 bg-card p-6 xl:col-span-2">
                        <h3 class="text-xl font-semibold">Users to review</h3>
                        <div class="mt-6 grid gap-3 lg:grid-cols-2">
                            @forelse ($suspiciousUsers as $user)
                                <div class="rounded-2xl border border-border/50 bg-secondary/20 p-4">
                                    <p class="font-semibold">{{ $user->name }}</p>
                                    <p class="mt-1 text-sm text-muted-foreground">{{ $user->email }} | {{ $user->snap_profiles_count }} listing(s) | {{ $user->reported_profiles_count }} reported</p>
                                    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="mt-3 flex flex-wrap gap-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="name" value="{{ $user->name }}">
                                        <input type="hidden" name="email" value="{{ $user->email }}">
                                        <input type="hidden" name="is_admin" value="{{ $user->is_admin ? 1 : 0 }}">
                                        <input type="hidden" name="is_active" value="{{ $user->is_active ? 1 : 0 }}">
                                        <input type="hidden" name="is_suspended" value="1">
                                        <button class="inline-flex h-9 items-center justify-center rounded-xl border border-destructive/40 bg-destructive/10 px-3 text-xs font-medium text-destructive" type="submit">Suspend user</button>
                                        <a href="{{ route('admin.index', ['tab' => 'users', 'users_page' => request('users_page', 1)]) }}" class="inline-flex h-9 items-center justify-center rounded-xl border border-border bg-background px-3 text-xs font-medium transition-all hover:bg-secondary">Open users</a>
                                    </form>
                                </div>
                            @empty
                                <p class="text-sm text-muted-foreground">No suspicious user signals right now.</p>
                            @endforelse
                        </div>
                    </section>
                </div>
            @endif

            @if ($activeTab === 'taxonomy')
                <div class="mt-8 grid gap-8 lg:grid-cols-2">
                    <section class="rounded-3xl border border-primary/20 bg-card p-6">
                        <h3 class="text-xl font-semibold">Categories</h3>
                        <form method="POST" action="{{ route('admin.categories.store') }}" class="mt-5 grid gap-3 md:grid-cols-[1fr_120px_auto_auto]">
                            @csrf
                            <input class="sf-field" name="name" placeholder="New category" required>
                            <input class="sf-field" type="number" min="0" name="sort_order" placeholder="Order">
                            <label class="flex items-center gap-2 text-sm text-muted-foreground"><input type="checkbox" name="is_nsfw" value="1"> NSFW</label>
                            <button class="inline-flex h-11 items-center justify-center rounded-xl bg-primary px-5 text-sm font-medium text-primary-foreground" type="submit">Add</button>
                        </form>
                        <div class="mt-6 overflow-x-auto rounded-2xl border border-border/50">
                            <table class="min-w-full text-left text-sm">
                                <thead class="bg-secondary/30 text-xs uppercase tracking-wide text-muted-foreground">
                                    <tr><th class="px-4 py-3">Name</th><th class="px-4 py-3">Order</th><th class="px-4 py-3">NSFW</th><th class="px-4 py-3">Actions</th></tr>
                                </thead>
                                <tbody class="divide-y divide-border/50">
                                    @foreach ($categories as $category)
                                        <tr class="align-middle">
                                            <form method="POST" action="{{ route('admin.categories.update', $category) }}">
                                                @csrf
                                                @method('PUT')
                                                <td class="px-4 py-3"><input class="sf-field" name="name" value="{{ $category->name }}" required></td>
                                                <td class="px-4 py-3 w-28"><input class="sf-field" type="number" min="0" name="sort_order" value="{{ $category->sort_order }}"></td>
                                                <td class="px-4 py-3"><label class="flex items-center gap-2 text-muted-foreground"><input type="checkbox" name="is_nsfw" value="1" @checked($category->is_nsfw)> NSFW</label></td>
                                                <td class="px-4 py-3"><button class="inline-flex h-10 items-center justify-center rounded-xl bg-primary px-4 text-sm font-medium text-primary-foreground" type="submit">Save</button></td>
                                            </form>
                                        </tr>
                                        <tr>
                                            <td class="px-4 pb-3 pt-0 text-xs text-muted-foreground" colspan="4">
                                                Slug: {{ $category->slug }}
                                                <form method="POST" action="{{ route('admin.categories.delete', $category) }}" class="ml-3 inline" onsubmit="return confirm('Delete this category?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="text-destructive hover:underline" type="submit">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-primary/20 bg-card p-6">
                        <h3 class="text-xl font-semibold">Tags</h3>
                        <p class="mt-2 text-sm text-muted-foreground">Tags are created during listing submission. Remove unsafe tags or mark adult-only tags as NSFW here.</p>
                        <div class="mt-6 overflow-x-auto rounded-2xl border border-border/50">
                            <table class="min-w-full text-left text-sm">
                                <thead class="bg-secondary/30 text-xs uppercase tracking-wide text-muted-foreground">
                                    <tr><th class="px-4 py-3">Name</th><th class="px-4 py-3">NSFW</th><th class="px-4 py-3">Actions</th></tr>
                                </thead>
                                <tbody class="divide-y divide-border/50">
                            @forelse ($tags as $tag)
                                <tr>
                                    <form method="POST" action="{{ route('admin.tags.update', $tag) }}">
                                        @csrf
                                        @method('PUT')
                                        <td class="px-4 py-3"><input class="sf-field" name="name" value="{{ $tag->name }}" required></td>
                                        <td class="px-4 py-3"><label class="flex items-center gap-2 text-muted-foreground"><input type="checkbox" name="is_nsfw" value="1" @checked($tag->is_nsfw)> NSFW</label></td>
                                        <td class="px-4 py-3"><button class="inline-flex h-10 items-center justify-center rounded-xl bg-primary px-4 text-sm font-medium text-primary-foreground" type="submit">Save</button></td>
                                    </form>
                                </tr>
                                <tr>
                                    <td class="px-4 pb-3 pt-0 text-xs text-muted-foreground" colspan="3">
                                        Slug: {{ $tag->slug }}
                                        <form method="POST" action="{{ route('admin.tags.delete', $tag) }}" class="ml-3 inline" onsubmit="return confirm('Delete this tag?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-destructive hover:underline" type="submit">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="px-4 py-4 text-sm text-muted-foreground" colspan="3">No tags yet.</td></tr>
                            @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            @endif
        </div>
    </section>
@endsection
