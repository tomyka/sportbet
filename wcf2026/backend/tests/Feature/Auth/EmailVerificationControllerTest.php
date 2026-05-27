<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

it('verifies email with a valid signed URL', function () {
    Event::fake([Verified::class]);
    $user = User::factory()->unverified()->create();

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)],
    );

    $this->actingAs($user)->getJson($url)->assertNoContent();

    $fresh = $user->fresh();
    assert($fresh !== null);
    expect($fresh->hasVerifiedEmail())->toBeTrue();
    Event::assertDispatched(Verified::class);
});

it('returns 204 if email already verified', function () {
    $user = User::factory()->create(); // already verified
    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)],
    );

    $this->actingAs($user)->getJson($url)->assertNoContent();
});

it('returns 403 if hash does not match', function () {
    $user = User::factory()->unverified()->create();
    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => 'wrong-hash'],
    );

    $this->actingAs($user)->getJson($url)->assertForbidden();
});

it('resends verification email', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->postJson('/api/v1/auth/email/resend')
        ->assertNoContent();
});

it('does not resend if already verified', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/v1/auth/email/resend')
        ->assertNoContent(); // idempotent 204
});
