<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class AnonymizeUser
{
    /**
     * Rewrite a user to the anonymized "Deleted Account" state and remove
     * their drafts — the same thing self-service account deletion does.
     * Callable both from ProfileController::confirmDestroy() and directly
     * from the console, for accounts soft-deleted outside that flow that
     * never had their name/email rewritten.
     */
    public function __invoke(User $user): void
    {
        DB::table('sessions')->where('user_id', $user->id)->delete();

        $user->sentMessages()->drafts()->delete();

        $user->forceFill([
            'name' => 'Deleted Account',
            'email' => 'deleted-'.$user->id.'@deleted.pennypost.invalid',
            'email_verified_at' => null,
            'remember_token' => null,
            'username' => null,
        ])->save();

        if (! $user->trashed()) {
            $user->delete();
        }
    }
}

