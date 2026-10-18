<?php

namespace App\Http\Controllers;

use App\Services\AppSettingsService;
use App\Services\VeniceChatService;
use App\Services\VeniceImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request, AppSettingsService $settings, VeniceChatService $venice, VeniceImageService $images): View
    {
        return view('settings.index', [
            'profileUser' => $request->user(),
            'appSettings' => $settings->defaults(),
            'modelOptions' => $venice->availableModels(),
            'imageModelOptions' => $images->availableModels(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:12', 'confirmed'],
        ]);

        $request->user()->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'] ? Hash::make($data['password']) : $request->user()->password,
        ]);

        return back()->with('status', 'Admin profile updated.');
    }

    public function updateApp(Request $request, AppSettingsService $settings): RedirectResponse
    {
        $data = $request->validate([
            'default_model' => ['required', 'string', 'max:120'],
            'default_image_model' => ['required', 'string', 'max:120'],
            'default_temperature' => ['required', 'numeric', 'between:0,2'],
            'default_max_tokens' => ['required', 'integer', 'min:128', 'max:4096'],
            'default_top_p' => ['required', 'numeric', 'between:0,1'],
            'summary_message_limit' => ['required', 'integer', 'min:4', 'max:40'],
            'memory_extraction_limit' => ['required', 'integer', 'min:1', 'max:6'],
            'venice_admin_key' => ['required', 'string'],
        ]);

        $settings->setMany('app', $data);

        return back()->with('status', 'App settings updated.');
    }
}
