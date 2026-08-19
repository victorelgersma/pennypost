<?php

use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

/**
 * Message::nextBatchFor() defaults to Laravel's now() (mutable Carbon),
 * while Message::canUnseal() calls CarbonImmutable::now() directly.
 * Carbon and CarbonImmutable each keep their own independent "testNow",
 * so both must be frozen together or one of the two checks will silently
 * fall back to the real wall-clock time.
 */
function freezeTimeAt(string $datetime): void
{
    Carbon::setTestNow(Carbon::parse($datetime, 'UTC'));
    CarbonImmutable::setTestNow(CarbonImmutable::parse($datetime, 'UTC'));
}

afterEach(function () {
    Carbon::setTestNow();
    CarbonImmutable::setTestNow();
});

// --- Saving and editing drafts -------------------------------------------------

test('a user can save a letter as a draft without a recipient', function () {
    $sender = User::factory()->create();

    $response = $this->actingAs($sender)->post('/messages', [
        'intent' => 'draft',
        'body' => 'Just getting my thoughts down...',
    ]);

    $draft = Message::first();

    $response->assertRedirect(route('messages.edit', $draft));
    $response->assertSessionHas('status', 'draft-saved');

    expect($draft->is_draft)->toBeTrue();
    expect($draft->sender_id)->toBe($sender->id);
    expect($draft->recipient_id)->toBeNull();
    expect($draft->scheduled_for)->toBeNull();
    expect($draft->sent_at)->toBeNull();
});

test('a draft can be saved with no body and no recipient at all', function () {
    $sender = User::factory()->create();

    $response = $this->actingAs($sender)->post('/messages', [
        'intent' => 'draft',
    ]);

    $response->assertSessionHasNoErrors();

    $draft = Message::first();
    expect($draft->is_draft)->toBeTrue();
    expect($draft->body)->toBe('');
});

test('a user can return to a draft later and update its recipient and body', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create();
    $draft = Message::factory()->for($sender, 'sender')->draft()->create(['body' => 'first pass']);

    $response = $this->actingAs($sender)->put("/messages/{$draft->id}", [
        'intent' => 'draft',
        'recipient_id' => $recipient->id,
        'body' => 'a much better version',
    ]);

    $response->assertRedirect(route('messages.edit', $draft));
    $response->assertSessionHas('status', 'draft-saved');

    $draft->refresh();
    expect($draft->is_draft)->toBeTrue();
    expect($draft->recipient_id)->toBe($recipient->id);
    expect($draft->body)->toBe('a much better version');
});

test('a user cannot view, edit, or overwrite someone elses draft', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $draft = Message::factory()->for($owner, 'sender')->draft()->create(['body' => 'private thoughts']);

    $this->actingAs($intruder)->get("/messages/{$draft->id}/edit")->assertNotFound();

    $this->actingAs($intruder)->put("/messages/{$draft->id}", [
        'intent' => 'draft',
        'body' => 'hijacked',
    ])->assertNotFound();

    expect($draft->fresh()->body)->toBe('private thoughts');
});

test('a user can delete their own draft', function () {
    $sender = User::factory()->create();
    $draft = Message::factory()->for($sender, 'sender')->draft()->create();

    $response = $this->actingAs($sender)->delete("/messages/{$draft->id}");

    $response->assertRedirect(route('messages.drafts'));
    $response->assertSessionHas('status', 'draft-deleted');
    $this->assertDatabaseMissing('messages', ['id' => $draft->id]);
});

test('a sealed letter cannot be deleted through the draft delete route', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create();
    $sealed = Message::factory()->for($sender, 'sender')->for($recipient, 'recipient')->create();

    $this->actingAs($sender)->delete("/messages/{$sealed->id}")->assertNotFound();

    $this->assertDatabaseHas('messages', ['id' => $sealed->id]);
});

// --- Sealing (sending) a draft --------------------------------------------------

test('sealing a draft requires a recipient and a body', function () {
    $sender = User::factory()->create();
    $draft = Message::factory()->for($sender, 'sender')->draft()->create(['recipient_id' => null, 'body' => '']);

    $response = $this->actingAs($sender)->put("/messages/{$draft->id}", [
        'intent' => 'send',
    ]);

    $response->assertSessionHasErrors(['recipient_id', 'body']);
    expect($draft->fresh()->is_draft)->toBeTrue();
});

test('sealing a draft turns it into a scheduled, non-draft letter', function () {
    freezeTimeAt('2026-08-10 09:00:00'); // Monday

    $sender = User::factory()->create();
    $recipient = User::factory()->create();
    $draft = Message::factory()->for($sender, 'sender')->draft()->create();

    $response = $this->actingAs($sender)->put("/messages/{$draft->id}", [
        'intent' => 'send',
        'recipient_id' => $recipient->id,
        'body' => 'Finally ready to send this.',
    ]);

    $response->assertRedirect(route('messages.sent'));
    $response->assertSessionHas('status', 'message-sent');

    $sealed = $draft->fresh();
    expect($sealed->is_draft)->toBeFalse();
    expect($sealed->scheduled_for->toDateString())->toBe('2026-08-14');
    expect($sealed->sent_at)->not->toBeNull();
});

test('a letter can be sent directly without ever having been saved as a draft', function () {
    freezeTimeAt('2026-08-10 09:00:00');

    $sender = User::factory()->create();
    $recipient = User::factory()->create();

    $response = $this->actingAs($sender)->post('/messages', [
        'intent' => 'send',
        'recipient_id' => $recipient->id,
        'body' => 'No detour through drafts.',
    ]);

    $response->assertRedirect(route('messages.sent'));

    $message = Message::first();
    expect($message->is_draft)->toBeFalse();
    expect($message->sent_at)->not->toBeNull();
});

// --- Listing pages ---------------------------------------------------------------

test('the drafts index only shows the current users own drafts', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $mine = Message::factory()->for($user, 'sender')->draft()->create();
    Message::factory()->for($user, 'sender')->create(); // sealed, should not appear
    Message::factory()->for($other, 'sender')->draft()->create(); // someone else's draft

    $response = $this->actingAs($user)->get('/messages/drafts');

    $response->assertOk();
    expect($response->viewData('drafts')->pluck('id')->all())->toBe([$mine->id]);
});

test('the sent index only shows sealed letters, never drafts', function () {
    $user = User::factory()->create();

    $sealed = Message::factory()->for($user, 'sender')->create();
    Message::factory()->for($user, 'sender')->draft()->create();

    $response = $this->actingAs($user)->get('/messages/sent');

    $response->assertOk();
    expect($response->viewData('messages')->pluck('id')->all())->toBe([$sealed->id]);
});

// --- Unsealing ---------------------------------------------------------------

test('a sealed letter can be unsealed back into an editable draft before the monday cutoff', function () {
    freezeTimeAt('2026-08-10 11:59:59'); // one second before the cutoff

    $sender = User::factory()->create();
    $recipient = User::factory()->create();
    $sealed = Message::factory()->for($sender, 'sender')->for($recipient, 'recipient')->create([
        'scheduled_for' => CarbonImmutable::parse('2026-08-16 12:00:00', 'UTC'),
        'sent_at' => CarbonImmutable::parse('2026-08-10 09:00:00', 'UTC'),
    ]);

    $response = $this->actingAs($sender)->post("/messages/{$sealed->id}/unseal");

    $response->assertRedirect(route('messages.edit', $sealed));
    $response->assertSessionHas('status', 'message-unsealed');

    $unsealed = $sealed->fresh();
    expect($unsealed->is_draft)->toBeTrue();
    expect($unsealed->scheduled_for)->toBeNull();
    expect($unsealed->sent_at)->toBeNull();
});

test('a sealed letter cannot be unsealed once the friday noon cutoff has passed', function () {
    freezeTimeAt('2026-08-14 12:00:01'); // one second after the cutoff

    $sender = User::factory()->create();
    $recipient = User::factory()->create();
    $sealed = Message::factory()->for($sender, 'sender')->for($recipient, 'recipient')->create([
        'scheduled_for' => CarbonImmutable::parse('2026-08-16 12:00:00', 'UTC'),
        'sent_at' => CarbonImmutable::parse('2026-08-10 09:00:00', 'UTC'),
    ]);

    $response = $this->actingAs($sender)->post("/messages/{$sealed->id}/unseal");

    $response->assertForbidden();
    expect($sealed->fresh()->is_draft)->toBeFalse();
});

test('a delivered letter can never be unsealed, even before what wouldve been the cutoff', function () {
    freezeTimeAt('2026-08-14 09:00:00'); // still before the cutoff, but already delivered

    $sender = User::factory()->create();
    $recipient = User::factory()->create();
    $delivered = Message::factory()->for($sender, 'sender')->for($recipient, 'recipient')->delivered()->create([
        'scheduled_for' => CarbonImmutable::parse('2026-08-16 12:00:00', 'UTC'),
        'sent_at' => CarbonImmutable::parse('2026-08-10 09:00:00', 'UTC'),
    ]);

    $response = $this->actingAs($sender)->post("/messages/{$delivered->id}/unseal");

    $response->assertForbidden();
    expect($delivered->fresh()->is_draft)->toBeFalse();
});

test('a user cannot unseal someone elses letter', function () {
    freezeTimeAt('2026-08-14 09:00:00');

    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $recipient = User::factory()->create();
    $sealed = Message::factory()->for($owner, 'sender')->for($recipient, 'recipient')->create([
        'scheduled_for' => CarbonImmutable::parse('2026-08-16 12:00:00', 'UTC'),
        'sent_at' => CarbonImmutable::parse('2026-08-10 09:00:00', 'UTC'),
    ]);

    $this->actingAs($intruder)->post("/messages/{$sealed->id}/unseal")->assertNotFound();

    expect($sealed->fresh()->is_draft)->toBeFalse();
});

// --- Full end-to-end lifecycle ---------------------------------------------------

test('the full draft, seal, unseal, edit, and reseal lifecycle works end to end', function () {
    // Saturday
    freezeTimeAt('2026-08-08 09:00:00'); 

    $sender = User::factory()->create();
    $recipient = User::factory()->create();

    // 1. Start a draft with no recipient yet.
    $this->actingAs($sender)->post('/messages', [
        'intent' => 'draft',
        'body' => 'Dear future reader,',
    ])->assertSessionHas('status', 'draft-saved');

    $draft = Message::first();
    expect($draft->is_draft)->toBeTrue();
    expect($draft->recipient_id)->toBeNull();

    // 2. Come back Sunday and add a recipient plus more text.
    freezeTimeAt('2026-08-09 9:00:00'); //Sunday

    $this->actingAs($sender)->put("/messages/{$draft->id}", [
        'intent' => 'draft',
        'recipient_id' => $recipient->id,
        'body' => 'Dear future reader, here is my update.',
    ])->assertSessionHas('status', 'draft-saved');

    $draft->refresh();
    expect($draft->is_draft)->toBeTrue();
    expect($draft->recipient_id)->toBe($recipient->id);

    // 3. Seal it before Monday, catching this week's batch.
    $this->actingAs($sender)->put("/messages/{$draft->id}", [
        'intent' => 'send',
        'recipient_id' => $recipient->id,
        'body' => 'Dear future reader, here is my final update.',
    ])->assertRedirect(route('messages.sent'));

    $sealed = $draft->fresh();
    expect($sealed->is_draft)->toBeFalse();
    expect($sealed->scheduled_for->toDateString())->toBe('2026-08-14');
    expect($sealed->sent_at)->not->toBeNull();

    // 4. Have second thoughts and unseal it.

    $this->actingAs($sender)->post("/messages/{$sealed->id}/unseal")
        ->assertRedirect(route('messages.edit', $sealed))
        ->assertSessionHas('status', 'message-unsealed');

    $unsealed = $sealed->fresh();
    expect($unsealed->is_draft)->toBeTrue();
    expect($unsealed->scheduled_for)->toBeNull();
    expect($unsealed->sent_at)->toBeNull();

    // 5. Edit it once more and reseal, still before the cutoff passes.
    $this->actingAs($sender)->put("/messages/{$unsealed->id}", [
        'intent' => 'send',
        'recipient_id' => $recipient->id,
        'body' => 'Dear future reader, this is really it this time.',
    ])->assertRedirect(route('messages.sent'));

    $final = $unsealed->fresh();
    expect($final->is_draft)->toBeFalse();
    expect($final->body)->toBe('Dear future reader, this is really it this time.');
    expect($final->scheduled_for->toDateString())->toBe('2026-08-14');

    // 6. It now shows up in Sent, and nowhere in Drafts.
    $this->actingAs($sender)->get('/messages/sent')
        ->assertOk()
        ->assertSee('this is really it this time');

    $this->actingAs($sender)->get('/messages/drafts')
        ->assertOk()
        ->assertDontSee('this is really it this time');

    // 7. Once the cutoff passes for good, it can no longer be unsealed.
    freezeTimeAt('2026-08-14 12:00:01');

    $this->actingAs($sender)->post("/messages/{$final->id}/unseal")->assertForbidden();
    expect($final->fresh()->is_draft)->toBeFalse();
});
