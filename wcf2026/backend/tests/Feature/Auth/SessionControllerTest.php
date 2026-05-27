<?php

use App\Models\User;

it('logs in via cookie session and returns the authenticated user', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('secret-pass'),
    ]);

    // Initialize session
    $this->get('/sanctum/csrf-cookie', ['referer' => config('app.url')])->assertNoContent();

    $this->postJson('/api/v1/auth/login', [
        'email' => 'test@example.com',
        'password' => 'secret-pass',
    ], ['referer' => config('app.url')])
        ->assertOk()
        ->assertJsonPath('data.email', 'test@example.com');

    $this->getJson('/api/v1/auth/me', ['referer' => config('app.url')])
        ->assertOk()
        ->assertJsonPath('data.id', $user->id);

    $this->postJson('/api/v1/auth/logout', [], ['referer' => config('app.url')])
        ->assertNoContent();
});
