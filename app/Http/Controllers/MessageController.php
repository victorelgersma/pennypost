<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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
                    'letterCount' => $thread->count(),
                ];
            })
            ->values();

        $drafts = $request->user()
            ->sentMessages()
            ->drafts()
            ->count();

        return view('correspondence.index', [
            'correspondences' => $correspondences,
            'draftCount' => $drafts,
        ]);
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

    $baseQuery = fn () => Message::query()
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
        });

    // The day a letter is grouped under — delivered_at for delivered mail,
    // sent_at for our own still-sealed letters, matching the ordering rule
    // used everywhere else in this thread.
    $dateExpr = 'date(coalesce(delivered_at, sent_at))';

    // One page per *day*, not per letter — several letters that landed (or
    // were sent) together should read together, the way a few envelopes
    // arriving in the same post would.
    $dates = $baseQuery()
        ->selectRaw("{$dateExpr} as page_date")
        ->distinct()
        ->orderByRaw("{$dateExpr} desc")
        ->pluck('page_date');

    $page = max(1, (int) $request->query('page', 1));
    $page = min($page, max(1, $dates->count()));
    $currentDate = $dates->get($page - 1);

    $letters = $currentDate
        ? $baseQuery()
            ->whereRaw("{$dateExpr} = ?", [$currentDate])
            ->orderByRaw('coalesce(delivered_at, sent_at) desc')
            ->get()
        : collect();

    $messages = new LengthAwarePaginator(
        $letters,
        $dates->count(),
        1,
        $page,
        ['path' => $request->url(), 'query' => $request->query()]
    );

    // Dates are sorted newest-first, so "older" is the next index along
    // and "newer" is the one before it.
    $olderDate = $dates->get($page);
    $newerDate = $page > 1 ? $dates->get($page - 2) : null;

    return view('correspondence.show', [
        'person' => $person,
        'messages' => $messages,
        'olderLetterDate' => $olderDate ? CarbonImmutable::parse($olderDate) : null,
        'newerLetterDate' => $newerDate ? CarbonImmutable::parse($newerDate) : null,
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
                'body' => ['nullable', 'string', 'max:'.config('pennypost.max_letter_length')],
            ], [
                'recipient_id.exists' => $notPennyPostMember,
                'body.max' => __('You have run out of ink.'),
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
            'body' => ['required', 'string', 'max:'.config('pennypost.max_letter_length')],
        ], [
            'recipient_id.required' => $notPennyPostMember,
            'recipient_id.exists' => $notPennyPostMember,
            'recipient_id.not_in' => "You can't send a message to yourself.", // TODO: rethink this - you can send messages to yourself in WhatsApp, why not here?
            'body.max' => __('You have run out of ink.'),
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
            ->with('status', 'message-sent')
            ->with('deliveryDayName', $message->scheduled_for->format('l'))
            ->with('deliveryDayOrdinal', $message->scheduled_for->format('jS'));
    }
}
