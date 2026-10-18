<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\ForgeMailer;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->to(route('dashboard', absolute: false));
        }

        return view('auth.login', ['captchaQuestion' => $this->refreshCaptcha('login_captcha')]);
    }

    public function register(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->to(route('dashboard', absolute: false));
        }

        return view('auth.register', ['captchaQuestion' => $this->refreshCaptcha('register_captcha')]);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'captcha_answer' => ['required', 'string'],
        ]);

        if (! $this->validateCaptcha($credentials['captcha_answer'], 'login_captcha')) {
            return back()->withInput()->withErrors(['captcha_answer' => 'The bot check answer was incorrect.']);
        }

        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()->withInput()->withErrors([
                'email' => 'The provided credentials could not be verified.',
            ]);
        }

        if (! $request->user()?->is_active || $request->user()?->is_suspended) {
            Auth::logout();

            return back()->withInput()->withErrors([
                'email' => 'This account is currently paused.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->to(route('dashboard', absolute: false));
    }

    public function signup(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'captcha_answer' => ['required', 'string'],
        ], [
            'email.unique' => 'The account could not be created with those details.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.min' => 'Use a password with at least 8 characters.',
        ]);

        if (! $this->validateCaptcha($data['captcha_answer'], 'register_captcha')) {
            return back()->withInput()->withErrors(['captcha_answer' => 'The bot check answer was incorrect.']);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_admin' => false,
            'is_active' => true,
            'is_suspended' => false,
        ]);

        app()->terminating(function () use ($user) {
            $this->sendVerificationLink($user);
        });

        Auth::login($user);
        $request->session()->regenerate();
        session(['verification_last_sent_at' => now()]);

        return redirect()->to(route('verification.notice', absolute: false))
            ->with('status', 'Your account is ready. The verification email is being sent.')
            ->with('ga_event', 'sign_up')
            ->with('ga_label', 'Snap Finder account created');
    }

    public function verificationNotice(): View|RedirectResponse
    {
        if (request()->user()?->hasVerifiedEmail()) {
            return redirect()->to(route('dashboard', absolute: false));
        }

        return view('auth.verify-email');
    }

    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect()->to(route('dashboard', absolute: false))->with('status', 'Email verified. You can create listings now.');
    }

    public function sendVerification(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->to(route('dashboard', absolute: false));
        }

        $lastSentAt = session('verification_last_sent_at');
        if ($lastSentAt && now()->diffInSeconds($lastSentAt) < 60) {
            return back()->withErrors(['email' => 'A verification email was just sent. Please wait about a minute before sending another one.']);
        }

        $sent = $this->sendVerificationLink($request->user());
        session(['verification_last_sent_at' => now()]);

        if (! $sent) {
            return back()->withErrors(['email' => 'The server could not hand the email to the mail system. Please try again in a minute or contact support.']);
        }

        return back()->with('status', 'Verification email sent. Check your inbox and spam folder.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to(route('welcome', absolute: false));
    }

    protected function sendVerificationLink(User $user): bool
    {
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->getKey(), 'hash' => sha1($user->getEmailForVerification())]
        );

        $body = "Verify your Snap Finder email\n\nClick this link to verify your account:\n{$url}\n\nThis link expires in 60 minutes.";
        $subject = 'Verify your Snap Finder email';

        return ForgeMailer::sendRaw($user->email, $user->name, $subject, $body, 'Snap Finder');
    }
}
