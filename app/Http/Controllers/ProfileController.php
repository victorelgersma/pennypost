<?php

namespace App\Http\Controllers;

use App\Actions\SendAccountDeletionLink;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Request account deletion — sends a confirmation link rather than
     * deleting immediately. The account is only removed once that link
     * is clicked, in confirmDestroy().
     */
    public function destroy(Request $request, SendAccountDeletionLink $sendAccountDeletionLink): RedirectResponse
    {
        $sendAccountDeletionLink($request->user());

        return Redirect::route('profile.edit')->with('status', 'account-deletion-requested');
    }

    /**
     * Actually delete the account, reached only via the signed link
     * emailed by destroy(). Logs out the current session if it belongs
     * to this user, and invalidates any other active sessions too.
     */
    public function confirmDestroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()?->id === $user->id) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        DB::table('sessions')->where('user_id', $user->id)->delete();

        // Drafts are private and were never seen by anyone else — safe to
        // remove outright, unlike sent/delivered letters.
        $user->sentMessages()->drafts()->delete();

        // Soft-delete + anonymize rather than hard-delete: the users table
        // is still referenced by sender_id/recipient_id on any letters this
        // person sent or received. A hard delete would cascade and take
        // those messages down with it — including letters the OTHER person
        // in the correspondence still has every right to keep.
        $user->forceFill([
            'name' => 'Deleted user',
            'email' => 'deleted-'.$user->id.'@deleted.pennypost.invalid',
            'email_verified_at' => null,
            'remember_token' => null,
        ])->save();

        $user->delete();

        return redirect()->to('/')->with('status', 'account-deleted');
    }
}
