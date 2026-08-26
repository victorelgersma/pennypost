<?php

use App\Models\User;
use App\Notifications\AccountDeletionNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('requesting account deletion sends a confirmation link rather than deleting immediately', function () {
    Notification::fake();
    $user = User::factory()->create();

    $response = $this->actingAs($user)->delete('/profile');

    $response->assertRedirect(route('profile.edit'));
    $response->assertSessionHas('status', 'account-deletion-requested');
    Notification::assertSentTo($user, AccountDeletionNotification::class);
    $this->assertNotNull($user->fresh());
});

test('visiting the signed deletion link permanently deletes the account and logs out the session', function () {
    $user = User::factory()->create();
    $url = URL::temporarySignedRoute('profile.destroy.confirm', now()->addMinutes(15), ['user' => $user->id]);

    $response = $this->actingAs($user)->get($url);

    $response->assertRedirect('/');
    $response->assertSessionHas('status', 'account-deleted');
    $this->assertGuest();
    $this->assertNull(User::find($user->id));
});

test('an unsigned deletion link is rejected and the account is kept', function () {
    $user = User::factory()->create();

    $this->get("/profile/delete/{$user->id}")->assertForbidden();
    $this->assertNotNull($user->fresh());
});
