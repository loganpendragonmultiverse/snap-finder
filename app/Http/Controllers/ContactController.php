<?php

namespace App\Http\Controllers;

use App\Support\ForgeMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function create(): View
    {
        return view('pages.contact', [
            'captchaQuestion' => session('contact_submitted')
                ? null
                : $this->refreshCaptcha('contact_captcha'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:4000'],
            'captcha_answer' => ['required', 'string'],
            'address' => ['nullable', 'string', 'max:0'],
        ]);

        if (! $this->validateCaptcha($data['captcha_answer'], 'contact_captcha')) {
            return back()->withInput()->withErrors(['captcha_answer' => 'The bot check answer was incorrect.']);
        }

        $body = "Snap Finder contact form\n\nName: {$data['name']}\nEmail: {$data['email']}\nSubject: {$data['subject']}\n\n{$data['message']}";
        $subject = '[Snap Finder] '.$data['subject'];

        $sent = config('mail.contact_address') ? ForgeMailer::sendRaw(config('mail.contact_address'), 'Instance operator', $subject, $body, 'Snap Finder') : false;

        if (! $sent) {
            return back()->withInput()->withErrors(['email' => 'The server could not send the message. The instance operator must configure contact email.']);
        }

        $request->session()->forget('contact_captcha');

        return redirect()->route('contact')
            ->with('contact_submitted', true);
    }
}
