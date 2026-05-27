<?php

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;

it('sends a password reset link to a registered email', function () {
    Notification::fake();
    $user = User::factory()->create();

    $this->postJson('/api/v1/auth/password/forgot', ['email' => $user->email])
         ->assertNoContent();

    Notification::assertSentTo($user, ResetPassword::class);
});

it('returns 204 for unknown email (no enumeration)', function () {
    $this->postJson('/api/v1/auth/password/forgot', ['email' => 'unknown@example.com'])
         ->assertNoContent();
});

it('returns 422 when email is missing', function () {
    $this->postJson('/api/v1/auth/password/forgot', [])
         ->assertUnprocessable()
         ->assertJsonValidationErrors(['email']);
});
