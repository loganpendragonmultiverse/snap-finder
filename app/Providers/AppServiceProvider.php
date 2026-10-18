<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        URL::forceRootUrl(rtrim((string) config('app.url'), '/'));
        if (str_starts_with(config('app.url'), 'https://')) URL::forceScheme('https');

        RateLimiter::for('age-gate', function (Request $request) {
            return Limit::perMinute(8)
                ->by('age-gate:'.$request->user()->getAuthIdentifier())
                ->response(function (Request $request, array $headers) {
                    return redirect()
                        ->back()
                        ->withErrors(['date_of_birth' => 'Too many attempts were submitted. Please wait one minute and try again.'])
                        ->withHeaders($headers);
                });
        });

        RateLimiter::for('login', function (Request $request) {
            $email = Str::lower(trim((string) $request->input('email')));

            return Limit::perMinute(10)
                ->by('login:'.hash('sha256', $email.'|'.$request->ip()))
                ->response(function (Request $request, array $headers) {
                    return redirect()
                        ->route('login')
                        ->withInput($request->only('email'))
                        ->withErrors(['email' => 'Too many login attempts were submitted. Please wait one minute and try again.'])
                        ->withHeaders($headers);
                });
        });
    }
}
