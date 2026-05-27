<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('implements MustVerifyEmail contract', function () {
    $user = User::factory()->unverified()->create();
    expect($user->hasVerifiedEmail())->toBeFalse();

    $user = User::factory()->create(); // factory creates verified by default
    expect($user->hasVerifiedEmail())->toBeTrue();
});

it('has profile fields with correct defaults', function () {
    $user = User::factory()->create([
        'display_name' => 'John Doe',
        'time_zone' => 'Europe/Vilnius',
        'locale' => 'en',
    ]);

    expect($user->display_name)->toBe('John Doe')
        ->and($user->time_zone)->toBe('Europe/Vilnius')
        ->and($user->is_global_admin)->toBeFalse();
});
