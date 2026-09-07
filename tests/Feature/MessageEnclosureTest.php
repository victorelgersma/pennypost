<?php

use App\Models\Message;
use App\Models\User;

test('a letter can be sent with multiple enclosures', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create();

    $response = $this->actingAs($sender)->post('/messages', [
        'intent' => 'send',
        'recipient_id' => $recipient->id,
        'body' => 'Two things for you.',
        'enclosures' => ['https://example.com/one', 'https://example.com/two'],
    ]);

    $response->assertRedirect(route('correspondence.show', $recipient));

    expect(Message::first()->enclosures)->toBe([
        'https://example.com/one',
        'https://example.com/two',
    ]);
});

test('blank enclosure rows are dropped rather than rejected', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create();

    $response = $this->actingAs($sender)->post('/messages', [
        'intent' => 'send',
        'recipient_id' => $recipient->id,
        'body' => 'One real link, one empty row.',
        'enclosures' => ['https://example.com/one', ''],
    ]);

    $response->assertSessionHasNoErrors();
    expect(Message::first()->enclosures)->toBe(['https://example.com/one']);
});

test('an invalid enclosure url is rejected', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create();

    $response = $this->actingAs($sender)->post('/messages', [
        'intent' => 'send',
        'recipient_id' => $recipient->id,
        'body' => 'Nope.',
        'enclosures' => ['not-a-url'],
    ]);

    $response->assertSessionHasErrors('enclosures.0');
});


test('a letter with enclosures shows them on the correspondence page', function () {
        $sender = User::factory()->create();
            $recipient = User::factory()->create();

            Message::factory()->delivered()->for($sender, 'sender')->for($recipient, 'recipient')->create([
                        'body' => 'See attached.',
                                'enclosures' => ['https://example.com/photo', 'https://example.com/menu'],
                                    ]);

                $response = $this->actingAs($recipient)->get(route('correspondence.show', $sender));

                $response->assertOk();
                    $response->assertSee('https://example.com/photo', false);
                    $response->assertSee('https://example.com/menu', false);
});

test('a letter without enclosures does not render the enclosure block', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create();

    Message::factory()->for($sender, 'sender')->for($recipient, 'recipient')->create([
        'body' => 'Just words this time.',
    ]);

    $response = $this->actingAs($recipient)->get(route('correspondence.show', $sender));

    $response->assertOk();
    $response->assertDontSee('ENCLOSED');
});

