<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function refreshCaptcha(string $key = 'captcha_answer'): string
    {
        $left = random_int(2, 9);
        $right = random_int(2, 9);
        session([$key => $left + $right]);

        return "{$left} + {$right}";
    }

    protected function validateCaptcha(string $answer, string $key = 'captcha_answer'): bool
    {
        $expected = session($key);
        session()->forget($key);

        return $expected !== null && (int) $answer === (int) $expected;
    }
}
