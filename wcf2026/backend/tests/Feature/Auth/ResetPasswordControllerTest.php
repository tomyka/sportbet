<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

it('resets password with a valid token', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $this->postJson('/api/v1/auth/password/reset', [
        'email' => $user->email,
        'token' => $token,
        'password' => 'brand-new-pass-123',
        'password_confirmation' => 'brand-new-pass-123',
    ])->assertNoContent();

    expect(Hash::check('brand-new-pass-123', $user->fresh()?->password ?? ''))->toBeTrue();
});

it('returns 422 with an invalid token', function () {
    $user = User::factory()->create();

    $this->postJson('/api/v1/auth/password/reset', [
        'email' => $user->email,
        'token' => 'bad-token',
        'password' => 'brand-new-pass-123',
        'password_confirmation' => 'brand-new-pass-123',
    ])->assertUnprocessable()
      ->assertJsonValidationErrors(['email']);
});
