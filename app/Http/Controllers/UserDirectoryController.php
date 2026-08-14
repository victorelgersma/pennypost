<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserDirectoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));

        $people = User::query()
            ->where('id', '!=', $request->user()->id)
            ->when($query !== '', fn ($builder) => $builder->where('name', 'like', "%{$query}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('directory.index', [
            'people' => $people,
            'query' => $query,
        ]);
    }
}