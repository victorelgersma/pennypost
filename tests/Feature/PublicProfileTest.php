<?php

use App\Models\User;

test('a public profile page shows the members name and join date', function () {
    User::factory()->create(['name' => 'Marianne Osei', 'username' => 'marianne']);

    $response = $this->get('/u/marianne');

    $response->assertOk();
    $response->assertSee('Marianne Osei');
});

test('the public profile lookup is case-insensitive', function () {
    User::factory()->create(['name' => 'Reuben Okafor', 'username' => 'reuben']);

    $this->get('/u/REUBEN')->assertOk()->assertSee('Reuben Okafor');
});

test('visiting a username with no matching member 404s', function () {
    $this->get('/u/nobody-here')->assertNotFound();
});

test('a guest sees the same write button as a logged in visitor', function () {
    User::factory()->create(['name' => 'Julian Ashworth', 'username' => 'julian']);

    $this->get('/u/julian')->assertOk()->assertSee('Write to Julian Ashworth');
});

test('a logged in visitor sees a write button on someone elses public profile', function () {
    $viewer = User::factory()->create();
    User::factory()->create(['name' => 'Julian Ashworth', 'username' => 'julian']);

    $response = $this->actingAs($viewer)->get('/u/julian');

    $response->assertOk();
    $response->assertSee('Write to Julian Ashworth');
});


test('a user sees the write button on their own public profile too, as a preview', function () {
        $user = User::factory()->create(['name' => 'Victor', 'username' => 'victor']);

            $response = $this->actingAs($user)->get('/u/victor');

            $response->assertOk();
                $response->assertSee('Write to Victor');
});

test('a guest is sent to log in when trying to write from a public profile link', function () {
    User::factory()->create(['username' => 'someone']);

    $response = $this->get('/messages/create?to_id=1&to_name=someone');

    $response->assertRedirect(route('login'));
});

test('a username can be set from profile settings', function () {
    $user = User::factory()->create(['username' => null]);

    $response = $this->actingAs($user)->patch('/profile', [
        'name' => $user->name,
        'email' => $user->email,
        'username' => 'Victor2',
    ]);

    $response->assertSessionHasNoErrors();
    expect($user->fresh()->username)->toBe('victor2');
});

test('a username must be unique', function () {
    User::factory()->create(['username' => 'taken']);
    $user = User::factory()->create(['username' => null]);

    $response = $this->actingAs($user)->patch('/profile', [
        'name' => $user->name,
        'email' => $user->email,
        'username' => 'taken',
    ]);

    $response->assertSessionHasErrors('username');
});

test('a username can be cleared to remove the public profile', function () {
    $user = User::factory()->create(['username' => 'goingaway']);

    $response = $this->actingAs($user)->patch('/profile', [
        'name' => $user->name,
        'email' => $user->email,
        'username' => '',
    ]);

    $response->assertSessionHasNoErrors();
    expect($user->fresh()->username)->toBeNull();
    $this->get('/u/goingaway')->assertNotFound();
});

test('deleting an account frees the username', function () {
    $user = User::factory()->create(['username' => 'freeme']);
    $url = \Illuminate\Support\Facades\URL::temporarySignedRoute('profile.destroy.confirm', now()->addMinutes(15), ['user' => $user->id]);

    $this->actingAs($user)->get($url);

    $this->get('/u/freeme')->assertNotFound();
});

test('a new user has no username by default', function () {
    $user = User::factory()->create();

    expect($user->username)->toBeNull();
});

