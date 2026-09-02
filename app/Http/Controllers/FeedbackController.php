<?php

namespace App\Http\Controllers;

use App\Notifications\FeedbackReceivedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class FeedbackController extends Controller
{
    protected string $inboxEmail = 'pennypost@vjbe.net';

    public function create(): View
    {
        return view('feedback');
    }

    public function store(Request $request): RedirectResponse
    {
        // Honeypot — same trick used on the login/register forms.
        if ($request->filled('website')) {
            return redirect()->route('feedback.create')->with('status', 'feedback-sent');
        }

        $throttleKey = 'feedback:'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'message' => __('Please wait a moment before sending more feedback.'),
            ]);
        }
        RateLimiter::hit($throttleKey, 300);

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        Notification::route('mail', $this->inboxEmail)->notify(
            new FeedbackReceivedNotification(
                name: $validated['name'] ?? null,
                email: $validated['email'] ?? null,
                message: $validated['message'],
            )
        );

        return redirect()->route('feedback.create')->with('status', 'feedback-sent');
    }
}