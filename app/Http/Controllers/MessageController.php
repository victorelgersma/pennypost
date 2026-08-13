<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $messages = $request->user()
            ->receivedMessages()
            ->delivered()
            ->with('sender')
            ->latest('delivered_at')
            ->paginate(15);

        return view('messages.inbox', ['messages' => $messages]);
    }

    public function sent(Request $request): View
    {
        $messages = $request->user()
            ->sentMessages()
            ->with('recipient')
            ->latest('created_at')
            ->paginate(15);

        return view('messages.sent', ['messages' => $messages]);
    }

    public function create(): View
    {
        return view('messages.create', [
            'nextBatch' => Message::nextBatchFor(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'recipient_id' => [
                'required',
                'integer',
                'exists:users,id',
                Rule::notIn([$request->user()->id]),
            ],
            'body' => ['required', 'string', 'max:2000'],
        ], [
            'recipient_id.not_in' => "You can't send a message to yourself.",
        ]);

        $request->user()->sentMessages()->create([
            'recipient_id' => $validated['recipient_id'],
            'body' => $validated['body'],
            'scheduled_for' => Message::nextBatchFor(),
        ]);

        return redirect()->route('messages.sent')->with('status', 'message-sent');
    }
}
