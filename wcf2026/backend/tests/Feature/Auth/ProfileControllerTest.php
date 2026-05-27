<?php

use App\Models\User;

it('updates the authenticated user profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->patchJson('/api/v1/auth/me', [
        'name' => 'Updated Name',
        'display_name' => 'Updy',
        'time_zone' => 'Europe/Vilnius',
        'locale' => 'en',
    ])->assertOk()
      ->assertJsonPath('data.name', 'Updated Name')
      ->assertJsonPath('data.display_name', 'Updy')
      ->assertJsonPath('data.time_zone', 'Europe/Vilnius');

    expect($user->fresh()?->name)->toBe('Updated Name');
});

it('returns 422 for invalid time zone', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->patchJson('/api/v1/auth/me', [
        'time_zone' => 'Not/ATimezone',
    ])->assertUnprocessable()
      ->assertJsonValidationErrors(['time_zone']);
});

it('returns 401 when unauthenticated', function () {
    $this->patchJson('/api/v1/auth/me', ['name' => 'Anyone'])->assertUnauthorized();
});
