<?php

use App\Models\Message;
use App\Models\User;

test('a user can compose a message to another user', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create();

    $response = $this->actingAs($sender)->post('/messages', [
        'recipient_id' => $recipient->id,
        'body' => 'Hey, hope you are well!',
    ]);

    $response->assertRedirect(route('messages.sent'));

    $this->assertDatabaseHas('messages', [
        'sender_id' => $sender->id,
        'recipient_id' => $recipient->id,
        'body' => 'Hey, hope you are well!',
    ]);

    expect(Message::first()->delivered_at)->toBeNull();
});

test('a user cannot send a message to themselves', function () {
    $sender = User::factory()->create();

    $response = $this->actingAs($sender)->post('/messages', [
        'recipient_id' => $sender->id,
        'body' => 'Talking to myself.',
    ]);

    $response->assertSessionHasErrors('recipient_id');
});

test('a message body is required and capped at 2000 characters', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create();

    $response = $this->actingAs($sender)->post('/messages', [
        'recipient_id' => $recipient->id,
        'body' => str_repeat('a', 2001),
    ]);

    $response->assertSessionHasErrors('body');
});

test('the inbox only shows delivered messages addressed to the current user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $delivered = Message::factory()->delivered()->create([
        'recipient_id' => $user->id,
        'body' => 'A delivered note.',
    ]);
    Message::factory()->create(['recipient_id' => $user->id]); // still scheduled
    Message::factory()->delivered()->create(['recipient_id' => $other->id]); // someone else's

    $response = $this->actingAs($user)->get('/inbox');

    $response->assertOk()->assertSee($delivered->body);
    expect($response->viewData('messages'))->toHaveCount(1);
});

test('the delivery command marks due messages as delivered', function () {
    $due = Message::factory()->create(['scheduled_for' => now()->subMinute()]);
    $notYetDue = Message::factory()->create(['scheduled_for' => now()->addWeek()]);

    $this->artisan('messages:deliver')->assertExitCode(0);

    expect($due->fresh()->delivered_at)->not->toBeNull();
    expect($notYetDue->fresh()->delivered_at)->toBeNull();
});
