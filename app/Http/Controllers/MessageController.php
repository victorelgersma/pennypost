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
            ->sent()
            ->with('recipient')
            ->latest('sent_at')
            ->paginate(15);

        return view('messages.sent', ['messages' => $messages]);
    }

    public function drafts(Request $request): View
    {
        $drafts = $request->user()
            ->sentMessages()
            ->drafts()
            ->with('recipient')
            ->latest('updated_at')
            ->paginate(15);

        return view('messages.drafts', ['drafts' => $drafts]);
    }

    public function create(): View
    {
        return view('messages.create', [
            'letter' => new Message,
            'nextBatch' => Message::nextBatchFor(),
        ]);
    }

    public function edit(Request $request, Message $message): View
    {
        abort_unless(
            $message->sender_id === $request->user()->id && $message->is_draft,
            404
        );

        return view('messages.create', [
            'letter' => $message,
            'nextBatch' => Message::nextBatchFor(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->save($request, new Message(['sender_id' => $request->user()->id]));
    }

    public function update(Request $request, Message $message): RedirectResponse
    {
        abort_unless(
            $message->sender_id === $request->user()->id && $message->is_draft,
            404
        );

        return $this->save($request, $message);
    }

    public function destroy(Request $request, Message $message): RedirectResponse
    {
        abort_unless(
            $message->sender_id === $request->user()->id && $message->is_draft,
            404
        );

        $message->delete();

        return redirect()->route('messages.drafts')->with('status', 'draft-deleted');
    }

    public function unseal(Request $request, Message $message): RedirectResponse
    {
        abort_unless($message->sender_id === $request->user()->id, 404);
        abort_unless($message->canUnseal(), 403);

        $message->fill([
            'is_draft' => true,
            'scheduled_for' => null,
            'sent_at' => null,
        ])->save();

        return redirect()->route('messages.edit', $message)->with('status', 'message-unsealed');
    }

    protected function save(Request $request, Message $message): RedirectResponse
    {
        $wantsToSend = $request->input('intent', 'send') === 'send';

        if (! $wantsToSend) {
            $validated = $request->validate([
                'recipient_id' => [
                    'nullable', 'integer', 'exists:users,id',
                    Rule::notIn([$request->user()->id]),
                ],
                'body' => ['nullable', 'string', 'max:2000'],
            ]);

            $message->sender_id = $request->user()->id;
            $message->fill([
                'recipient_id' => $validated['recipient_id'] ?? null,
                'body' => $validated['body'] ?? '',
                'is_draft' => true,
                'scheduled_for' => null,
                'sent_at' => null,
            ])->save();

            return redirect()->route('messages.edit', $message)->with('status', 'draft-saved');
        }

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

        $message->sender_id = $request->user()->id;
        $message->fill([
            'recipient_id' => $validated['recipient_id'],
            'body' => $validated['body'],
            'is_draft' => false,
            'scheduled_for' => Message::nextBatchFor(),
            'sent_at' => now(),
        ])->save();

        return redirect()->route('messages.sent')->with('status', 'message-sent');
    }
}