<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserDirectoryController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;
        $query = trim((string) $request->query('q', ''));

        $people = User::query()
            ->where('id', '!=', $userId)
            ->whereNotNull('name')
            ->when($query !== '', fn ($builder) => $builder->where('name', 'like', "%{$query}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        // Same visibility rule as everywhere else: a delivered letter either
        // way, or the current user's own sealed-but-undelivered letter,
        // counts as "there's a correspondence here."
        $correspondentIds = Message::query()
            ->sent()
            ->where(function ($q) use ($userId) {
                $q->where('sender_id', $userId)->orWhere('recipient_id', $userId);
            })
            ->where(function ($q) use ($userId) {
                $q->whereNotNull('delivered_at')->orWhere('sender_id', $userId);
            })
            ->get(['sender_id', 'recipient_id'])
            ->map(fn (Message $m) => $m->sender_id === $userId ? $m->recipient_id : $m->sender_id)
            ->unique()
            ->flip();

        return view('directory.index', [
            'people' => $people,
            'query' => $query,
            'correspondentIds' => $correspondentIds,
        ]);
    }
}