<?php

use App\Models\Message;
use App\Models\User;

test('a user can compose a message to another user', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create();

    $response = $this->actingAs($sender)->post('/messages', [
        'intent' => 'send',
        'recipient_id' => $recipient->id,
        'body' => 'Hey, hope you are well!',
    ]);

    $response->assertRedirect(route('correspondence.show', $recipient));

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

test('a message body is required and capped at 20000 characters', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create();

    $response = $this->actingAs($sender)->post('/messages', [
        'recipient_id' => $recipient->id,
        'body' => str_repeat('a', 20001),
    ]);

    $response->assertSessionHasErrors('body');
});


test('a correspondence thread only shows delivered messages from the other person, plus the current users own sealed ones', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $unrelated = User::factory()->create();

    Message::factory()->delivered()->create([
        'sender_id' => $other->id, 'recipient_id' => $user->id, 'body' => 'A delivered note.',
    ]);
    Message::factory()->create([
        'sender_id' => $other->id, 'recipient_id' => $user->id, 'body' => 'Still sealed, not delivered yet.',
    ]);
    Message::factory()->delivered()->create([
        'sender_id' => $other->id, 'recipient_id' => $unrelated->id, 'body' => "Someone else's letter.",
    ]);

    $response = $this->actingAs($user)->get(route('correspondence.show', $other));

    $response->assertOk();
    $response->assertSee('A delivered note.');
    $response->assertDontSee('Still sealed, not delivered yet.');
    $response->assertDontSee("Someone else's letter.");
});
test('the delivery command marks due messages as delivered', function () {
    $due = Message::factory()->create(['scheduled_for' => now()->subMinute()]);
    $notYetDue = Message::factory()->create(['scheduled_for' => now()->addWeek()]);

    $this->artisan('messages:deliver')->assertExitCode(0);

    expect($due->fresh()->delivered_at)->not->toBeNull();
    expect($notYetDue->fresh()->delivered_at)->toBeNull();
});

test('omitting intent entirely defaults to saving a draft, never sending', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create();

    $response = $this->actingAs($sender)->post('/messages', [
        // no 'intent' key at all — simulates the field being dropped from
        // the submission (e.g. a submit button losing its form association)
        'recipient_id' => $recipient->id,
        'body' => 'Meant to keep drafting this.',
    ]);

    $message = Message::first();

    $response->assertRedirect(route('messages.edit', $message));
    expect($message->is_draft)->toBeTrue();
    expect($message->sent_at)->toBeNull();
    expect($message->scheduled_for)->toBeNull();
});

test('an unrecognized intent value is rejected rather than falling through to send', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create();

    $response = $this->actingAs($sender)->post('/messages', [
        'intent' => 'delete-everything', // garbage / tampered value
        'recipient_id' => $recipient->id,
        'body' => 'This should never send.',
    ]);

    $response->assertStatus(422);
    expect(Message::count())->toBe(0);
});
