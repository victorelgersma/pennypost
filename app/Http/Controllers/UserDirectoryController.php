<?php

namespace App\Http\Controllers;

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

        // "Members" = accounts that have completed onboarding (i.e. have a
        // name) — same definition the directory listing itself uses.
        $totalMembers = User::whereNotNull('name')->count();

        return view('directory.index', [
            'people' => $people,
            'query' => $query,
            'totalMembers' => $totalMembers,
        ]);
    }
}
