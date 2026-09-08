<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicProfileController extends Controller
{
    public function show(string $username): View
    {
        $profile = User::query()
            ->where('username', Str::lower($username))
            ->whereNotNull('name')
            ->firstOrFail();

        return view('profile.public', ['profile' => $profile]);
    }
}
