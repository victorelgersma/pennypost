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
     * One tile per person the current user has a correspondence with:
     * anyone they've exchanged delivered letters with, plus anyone
     * they've sealed a letter to that's still awaiting delivery. Someone
     * else's letter to you that hasn't been delivered yet never counts —
     * that would spoil the whole "no peeking before post day" premise.
     */
    public function index(Request $request): View
    {
        $userId = $request->user()->id;

        $messages = Message::query()
            ->sent()
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)->orWhere('recipient_id', $userId);
            })
            ->where(function ($query) use ($userId) {
                $query->whereNotNull('delivered_at')->orWhere('sender_id', $userId);
            })
            ->with(['sender', 'recipient'])
            ->orderByDesc('sent_at')
            ->get(['id', 'sender_id', 'recipient_id', 'sent_at']);

        // Because $messages is already sorted newest-first, the first
        // message groupBy() encounters for each "other person" key is
        // necessarily that thread's most recent letter — so the groups
        // come out in the right order for free.
        $correspondences = $messages
            ->groupBy(fn (Message $m) => $m->sender_id === $userId ? $m->recipient_id : $m->sender_id)
            ->map(function ($thread) use ($userId) {
                $latest = $thread->first();

                return (object) [
                    'person' => $latest->sender_id === $userId ? $latest->recipient : $latest->sender,
                ];
            })
            ->values();

        return view('correspondence.index', ['correspondences' => $correspondences]);
    }

    /**
     * The thread with one person: every delivered letter between you two,
     * plus any of your own sealed-but-undelivered letters to them. Their
     * undelivered letters to you are excluded — those stay hidden until
     * post day.
     */
    public function show(Request $request, User $person): View
    {
        $userId = $request->user()->id;

        abort_if($person->id === $userId, 404);

        $messages = Message::query()
            ->sent()
            ->where(function ($query) use ($userId, $person) {
                $query->where(function ($q) use ($userId, $person) {
                    $q->where('sender_id', $userId)->where('recipient_id', $person->id);
                })->orWhere(function ($q) use ($userId, $person) {
                    $q->where('sender_id', $person->id)->where('recipient_id', $userId);
                });
            })
            ->where(function ($query) use ($userId) {
                $query->whereNotNull('delivered_at')->orWhere('sender_id', $userId);
            })
            ->orderByRaw('COALESCE(delivered_at, sent_at) asc')
            ->get();

        return view('correspondence.show', [
            'person' => $person,
            'messages' => $messages,
        ]);
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

        $notPennyPostMember = "That doesn't look like a Penny Post member — pick someone from the suggestions.";

        if (! $wantsToSend) {
            $validated = $request->validate([
                'recipient_id' => [
                    'nullable', 'integer', 
                    Rule::exists('users', 'id')->whereNull('deleted_at'),
                    Rule::notIn([$request->user()->id]),
                ],
                'body' => ['nullable', 'string', 'max:2000'],
            ], [
                'recipient_id.exists' => $notPennyPostMember,
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
                Rule::exists('users', 'id')->whereNull('deleted_at'),
                Rule::notIn([$request->user()->id]),
            ],
            'body' => ['required', 'string', 'max:2000'],
        ], [
            'recipient_id.required' => $notPennyPostMember,
            'recipient_id.exists' => $notPennyPostMember,
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

        return redirect()
            ->route('correspondence.show', $message->recipient)
            ->with('status', 'message-sent');
    }
}
