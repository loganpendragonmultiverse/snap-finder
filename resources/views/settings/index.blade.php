@extends('layouts.app', ['title' => 'Settings | Pendragon Chat Bot'])

@section('content')
    <main class="stack-layout">
        <section class="panel">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">Control Room</p>
                    <h2>Settings</h2>
                    <p class="panel-copy">Manage the admin identity, Venice key, and default generation profile without editing environment files every time.</p>
                </div>
            </div>

            <div class="dual-grid">
                <section class="panel nested-panel">
                    <h3>Admin Profile</h3>
                    <form method="POST" action="{{ route('settings.profile') }}" class="stack-form compact-form">
                        @csrf
                        @method('PUT')
                        <label><span>Name</span><input type="text" name="name" value="{{ old('name', $profileUser->name) }}" required></label>
                        <label><span>Email</span><input type="email" name="email" value="{{ old('email', $profileUser->email) }}" required></label>
                        <label><span>New Password</span><input type="password" name="password" minlength="12"></label>
                        <label><span>Confirm Password</span><input type="password" name="password_confirmation" minlength="12"></label>
                        <button type="submit" class="primary-button">Update Admin Profile</button>
                    </form>
                </section>

                <section class="panel nested-panel">
                    <h3>App Defaults</h3>
                    <form method="POST" action="{{ route('settings.app') }}" class="stack-form compact-form">
                        @csrf
                        @method('PUT')
                        <label>
                            <span>Default Model</span>
                            <select name="default_model" required>
                                @foreach ($modelOptions as $modelId => $model)
                                    <option value="{{ $modelId }}" @selected($appSettings['default_model'] === $modelId) @disabled(($model['status'] ?? 'active') !== 'active')>
                                        {{ $model['label'] }}@if (($model['status'] ?? 'active') !== 'active') (planned)@endif
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span>Default Image Model</span>
                            <select name="default_image_model" required>
                                @foreach ($imageModelOptions as $modelId => $model)
                                    <option value="{{ $modelId }}" @selected($appSettings['default_image_model'] === $modelId) @disabled(($model['status'] ?? 'active') !== 'active')>
                                        {{ $model['label'] }}@if (($model['status'] ?? 'active') !== 'active') (planned)@endif
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <div class="triple-grid">
                            <label><span>Temp</span><input type="number" name="default_temperature" step="0.01" min="0" max="2" value="{{ $appSettings['default_temperature'] }}" required></label>
                            <label><span>Max Tokens</span><input type="number" name="default_max_tokens" min="128" max="4096" value="{{ $appSettings['default_max_tokens'] }}" required></label>
                            <label><span>Top P</span><input type="number" name="default_top_p" step="0.01" min="0" max="1" value="{{ $appSettings['default_top_p'] }}" required></label>
                        </div>
                        <label><span>Summary Message Limit</span><input type="number" name="summary_message_limit" min="4" max="40" value="{{ $appSettings['summary_message_limit'] }}" required></label>
                        <label><span>Memory Extraction Limit</span><input type="number" name="memory_extraction_limit" min="1" max="6" value="{{ $appSettings['memory_extraction_limit'] }}" required></label>
                        <label><span>Venice Admin Key</span><textarea name="venice_admin_key" rows="4" required>{{ $appSettings['venice_admin_key'] }}</textarea></label>
                        <button type="submit" class="secondary-button">Update App Settings</button>
                    </form>
                </section>
            </div>
        </section>
    </main>
@endsection
