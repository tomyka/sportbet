<?php

use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;

it('registers a new user and sends verification email', function () {
    Event::fake([Registered::class]);

    $this->postJson('/api/v1/auth/register', [
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'password' => 'secret-pass-123',
        'password_confirmation' => 'secret-pass-123',
    ])->assertCreated()
      ->assertJsonPath('data.email', 'alice@example.com')
      ->assertJsonPath('data.email_verified', false);

    expect(User::where('email', 'alice@example.com')->exists())->toBeTrue();
    Event::assertDispatched(Registered::class);
});

it('records an audit event on register', function () {
    Event::fake([Registered::class]);

    $this->postJson('/api/v1/auth/register', [
        'name' => 'Audrey',
        'email' => 'audrey@example.com',
        'password' => 'secret-pass-123',
        'password_confirmation' => 'secret-pass-123',
    ])->assertCreated();

    expect(AuditEvent::where('action', 'user.register')->exists())->toBeTrue();
});

it('returns 422 when email already taken', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    $this->postJson('/api/v1/auth/register', [
        'name' => 'Bob',
        'email' => 'existing@example.com',
        'password' => 'secret-pass-123',
        'password_confirmation' => 'secret-pass-123',
    ])->assertUnprocessable()
      ->assertJsonValidationErrors(['email']);
});

it('returns 422 when password confirmation does not match', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Carol',
        'email' => 'carol@example.com',
        'password' => 'secret-pass-123',
        'password_confirmation' => 'wrong',
    ])->assertUnprocessable()
      ->assertJsonValidationErrors(['password']);
});

it('returns 422 when password is too short', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Dave',
        'email' => 'dave@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
    ])->assertUnprocessable()
      ->assertJsonValidationErrors(['password']);
});
