<?php

namespace App\Actions;

use App\Models\User;
use App\Notifications\LoginLinkNotification;
use Illuminate\Support\Facades\URL;

class SendLoginLink
{
    public function __invoke(User $user): void
    {
        $url = URL::temporarySignedRoute(
            'login.verify',
            now()->addMinutes(15),
            ['user' => $user->id]
        );

        $user->notify(new LoginLinkNotification($url));
    }
}
