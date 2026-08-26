<?php

use App\Models\User;
use App\Notifications\LoginLinkNotification;
use Illuminate\Support\Facades\Notification;

test('registration screen can be rendered', function () {
    $this->get('/register')->assertStatus(200);
});

test('a new user can register with just an email and receives a login link', function () {
    Notification::fake();

    $response = $this->post('/register', ['email' => 'newcomer@example.com']);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('status', 'login-link-sent');

    $user = User::where('email', 'newcomer@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBeNull();
    expect($user->email_verified_at)->toBeNull();

    Notification::assertSentTo($user, LoginLinkNotification::class);
});

test('registering with an email already in use resends a login link rather than erroring', function () {
    Notification::fake();
    $existing = User::factory()->create();

    $response = $this->post('/register', ['email' => $existing->email]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHasNoErrors();
    $this->assertDatabaseCount('users', 1);
});