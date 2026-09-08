<?php

use App\Models\Message;
use App\Models\User;

test('anonymizing a previously soft-deleted user rewrites its name and email and frees its drafts', function () {
    $user = User::factory()->create(['name' => 'Old Victor', 'email' => 'victor@vjbe.net', 'username' => 'victor']);
    $draft = Message::factory()->for($user, 'sender')->draft()->create();
    $user->delete();

    $this->artisan('users:anonymize', ['user' => 'victor@vjbe.net'])
        ->expectsConfirmation('Anonymize Old Victor (victor@vjbe.net)? This deletes their drafts and cannot be undone.', 'yes')
        ->assertExitCode(0);

    $fresh = User::withTrashed()->find($user->id);
    expect($fresh->name)->toBe('Deleted Account');
    expect($fresh->email)->toBe('deleted-'.$user->id.'@deleted.pennypost.invalid');
    expect($fresh->username)->toBeNull();
    $this->assertDatabaseMissing('messages', ['id' => $draft->id]);
});

test('declining the confirmation does nothing', function () {
    $user = User::factory()->create(['name' => 'Someone']);
    $user->delete();

    $this->artisan('users:anonymize', ['user' => (string) $user->id])
        ->expectsConfirmation("Anonymize Someone ({$user->email})? This deletes their drafts and cannot be undone.", 'no')
        ->assertExitCode(0);

    expect(User::withTrashed()->find($user->id)->name)->toBe('Someone');
});

test('anonymizing a user that cannot be found fails cleanly', function () {
    $this->artisan('users:anonymize', ['user' => 'nobody@example.com'])
        ->assertExitCode(1);
});

