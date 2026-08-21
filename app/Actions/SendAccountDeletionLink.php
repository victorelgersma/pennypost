<?php

namespace App\Actions;

use App\Models\User;
use App\Notifications\AccountDeletionNotification;
use Illuminate\Support\Facades\URL;

class SendAccountDeletionLink
{
    public function __invoke(User $user): void
    {
        $url = URL::temporarySignedRoute(
            'profile.destroy.confirm',
            now()->addMinutes(15),
            ['user' => $user->id]
        );

        $user->notify(new AccountDeletionNotification($url));
    }
}
