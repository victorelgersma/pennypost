<?php

use App\Models\Message;
use App\Models\User;

test('the command lists messages addressed to a user without changing anything by default', function () {
    $sender = User::factory()->create();
    $from = User::factory()->create();
    $to = User::factory()->create();

    $message = Message::factory()->for($sender, 'sender')->for($from, 'recipient')->create();

    $this->artisan('messages:reassign-recipient', ['from' => $from->email, 'to' => $to->email])
        ->assertExitCode(0);

    expect($message->fresh()->recipient_id)->toBe($from->id);
});

test('the command reassigns messages when run with --force', function () {
    $sender = User::factory()->create();
    $from = User::factory()->create();
    $to = User::factory()->create();

    $message = Message::factory()->for($sender, 'sender')->for($from, 'recipient')->create();

    $this->artisan('messages:reassign-recipient', ['from' => $from->email, 'to' => $to->email, '--force' => true])
        ->assertExitCode(0);

    expect($message->fresh()->recipient_id)->toBe($to->id);
});

test('the command works by numeric id and finds trashed accounts too', function () {
    $sender = User::factory()->create();
    $from = User::factory()->create();
    $to = User::factory()->create();
    $from->delete();

    $message = Message::factory()->for($sender, 'sender')->for($from, 'recipient')->create();

    $this->artisan('messages:reassign-recipient', ['from' => (string) $from->id, 'to' => (string) $to->id, '--force' => true])
        ->assertExitCode(0);

    expect($message->fresh()->recipient_id)->toBe($to->id);
});

test('the command fails cleanly when a user cannot be found', function () {
    $to = User::factory()->create();

    $this->artisan('messages:reassign-recipient', ['from' => 'nobody@example.com', 'to' => $to->email])
        ->assertExitCode(1);
});

