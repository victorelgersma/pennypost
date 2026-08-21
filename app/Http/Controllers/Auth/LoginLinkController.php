<?php

namespace App\Http\Controllers\Auth;

use App\Actions\SendLoginLink;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginLinkController extends Controller
{
    public function store(Request $request, SendLoginLink $sendLoginLink): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $throttleKey = Str::lower($request->input('email')).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            throw ValidationException::withMessages([
                'email' => __('Please wait a moment before requesting another link.'),
            ]);
        }

        RateLimiter::hit($throttleKey, 60);

        // Deliberately silent on whether the email exists, same as the old
        // forgot-password flow — avoids leaking which emails are registered.
        if ($user = User::where('email', $request->input('email'))->first()) {
            $sendLoginLink($user);
        }

        return redirect()->route('login')->with('status', 'login-link-sent');
    }

    public function authenticate(Request $request, User $user): RedirectResponse
    {
        if ($user->email_verified_at === null) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->intended(route('correspondence.index', absolute: false));
    }
}