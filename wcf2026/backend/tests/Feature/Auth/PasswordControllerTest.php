<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('changes password when current password is correct', function () {
    $user = User::factory()->create(['password' => Hash::make('old-password')]);

    $this->actingAs($user)->postJson('/api/v1/auth/password', [
        'current_password' => 'old-password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])->assertNoContent();

    expect(Hash::check('new-password-123', $user->fresh()?->password ?? ''))->toBeTrue();
});

it('returns 422 when current password is wrong', function () {
    $user = User::factory()->create(['password' => Hash::make('old-password')]);

    $this->actingAs($user)->postJson('/api/v1/auth/password', [
        'current_password' => 'wrong-password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])->assertUnprocessable()
      ->assertJsonValidationErrors(['current_password']);
});

it('returns 401 when unauthenticated', function () {
    $this->postJson('/api/v1/auth/password', [
        'current_password' => 'old',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])->assertUnauthorized();
});
