<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MessageController extends Controller
{
    /**
     * One row per person the current user has an exchange of delivered
     * letters with, newest correspondence first.
     */
    public function index(Request $request): View
    {
        $userId = $request->user()->id;

        $messages = Message::query()
            ->delivered()
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)->orWhere('recipient_id', $userId);
            })
            ->with(['sender', 'recipient'])
            ->orderByDesc('delivered_at')
            ->get();

        // Because $messages is already sorted newest-first, the first
        // message groupBy() encounters for each "other person" key is
        // necessarily that thread's most recent letter — so the groups
        // come out in the right order for free.
        $correspondences = $messages
            ->groupBy(fn (Message $m) => $m->sender_id === $userId ? $m->recipient_id : $m->sender_id)
            ->map(function ($thread) use ($userId) {
                $latest = $thread->first();
                $person = $latest->sender_id === $userId ? $latest->recipient : $latest->sender;

                return (object) [
                    'person' => $person,
                    'latest' => $latest,
                    'count' => $thread->count(),
                ];
            })
            ->values();

        return view('correspondence.index', ['correspondences' => $correspondences]);
    }

    /**
     * The full delivered thread between the current user and one other
     * person, oldest first.
     */
    public function show(Request $request, User $person): View
    {
        $userId = $request->user()->id;

        abort_if($person->id === $userId, 404);

        $messages = Message::query()
            ->delivered()
            ->where(function ($query) use ($userId, $person) {
                $query->where(function ($q) use ($userId, $person) {
                    $q->where('sender_id', $userId)->where('recipient_id', $person->id);
                })->orWhere(function ($q) use ($userId, $person) {
                    $q->where('sender_id', $person->id)->where('recipient_id', $userId);
                });
            })
            ->orderBy('delivered_at')
            ->get();

        return view('correspondence.show', [
            'person' => $person,
            'messages' => $messages,
        ]);
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