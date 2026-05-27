<?php

use App\Models\AuditEvent;
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

it('records an audit event on login', function () {
    User::factory()->create(['email' => 'audit@example.com', 'password' => bcrypt('secret-pass')]);

    $this->get('/sanctum/csrf-cookie', ['referer' => config('app.url')]);
    $this->postJson('/api/v1/auth/login', ['email' => 'audit@example.com', 'password' => 'secret-pass'],
        ['referer' => config('app.url')]);

    expect(AuditEvent::where('action', 'user.login')->exists())->toBeTrue();
});

it('records an audit event on logout', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $this->postJson('/api/v1/auth/logout', [], ['referer' => config('app.url')]);

    expect(AuditEvent::where('action', 'user.logout')->exists())->toBeTrue();
});
