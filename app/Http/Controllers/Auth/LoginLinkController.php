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
    /**
     * Single entry point for the app — no separate registration. If the
     * email doesn't match an existing account, one is created here with
     * no name yet; authenticate() below routes them to onboarding to set
     * it after they click the link.
     */
    public function store(Request $request, SendLoginLink $sendLoginLink): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email', 'max:255']]);

        // Honeypot: a field real users never see or fill. Bots that auto-fill
        // every input on a form will trip it. Pretend success rather than
        // erroring, so we don't tip off the bot that it was caught.
        if ($request->filled('website')) {
            return redirect()->route('login')->with('status', 'login-link-sent');
        }

        $email = Str::lower($request->input('email'));

        // Per-requester throttle: stops rapid resubmission from one source.
        $ipThrottleKey = $email.'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($ipThrottleKey, 3)) {
            throw ValidationException::withMessages([
                'email' => __('Please wait a moment before requesting another link.'),
            ]);
        }
        RateLimiter::hit($ipThrottleKey, 60);

        $user = User::where('email', $email)->first();
        $isUnverified = $user === null || $user->email_verified_at === null;

        // Per-recipient throttle for addresses that have never proven
        // ownership by clicking a link. This is the one that actually
        // matters: it's keyed on the EMAIL, not the requester, so rotating
        // IPs or using different browsers doesn't help an attacker at all.
        // Once someone clicks their first link, email_verified_at gets set
        // and they graduate to the lighter throttle above for their own use.
        if ($isUnverified) {
            $emailThrottleKey = 'unverified-login-link:'.$email;

            if (RateLimiter::tooManyAttempts($emailThrottleKey, 1)) {
                return redirect()->route('login')->with('status', 'login-link-sent');
            }

            RateLimiter::hit($emailThrottleKey, 300); // 5 minutes
        }

        $user ??= User::create(['email' => $email]);

        $sendLoginLink($user);

        return redirect()->route('login')->with('status', 'login-link-sent');
    }

    public function authenticate(Request $request, User $user): RedirectResponse
    {
        if ($user->email_verified_at === null) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        if ($user->name === null) {
            return redirect()->route('onboarding.name');
        }

        return redirect()->intended(route('correspondence.index', absolute: false));
    }
}
