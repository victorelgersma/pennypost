<?php

use App\Models\Message;
use Carbon\CarbonImmutable;

test('a message sent before friday noon utc goes out the same week', function () {
    $sentAt = CarbonImmutable::parse('2026-08-10 09:00:00', 'UTC'); // Monday

    $batch = Message::nextBatchFor($sentAt);

    expect($batch->toDateString())->toBe('2026-08-16')
        ->and($batch->format('H:i'))->toBe('12:00');
});

test('a message sent exactly at the friday noon utc cutoff still makes that batch', function () {
    $batch = Message::nextBatchFor(CarbonImmutable::parse('2026-08-14 12:00:00', 'UTC'));

    expect($batch->toDateString())->toBe('2026-08-16');
});

test('a message sent just after the friday noon utc cutoff waits an extra week', function () {
    $batch = Message::nextBatchFor(CarbonImmutable::parse('2026-08-14 12:00:01', 'UTC'));

    expect($batch->toDateString())->toBe('2026-08-23');
});

test('a message sent on saturday waits for the following weeks batch', function () {
    $batch = Message::nextBatchFor(CarbonImmutable::parse('2026-08-15 10:00:00', 'UTC'));

    expect($batch->toDateString())->toBe('2026-08-23');
});

test('a message sent sunday morning misses same-day delivery', function () {
    $batch = Message::nextBatchFor(CarbonImmutable::parse('2026-08-16 09:00:00', 'UTC'));

    expect($batch->toDateString())->toBe('2026-08-23');
});
