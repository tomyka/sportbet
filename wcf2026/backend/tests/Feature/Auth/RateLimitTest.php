<?php

use Illuminate\Support\Facades\Cache;

beforeEach(fn () => Cache::flush());

it('rate-limits login to 5 per minute per IP', function () {
    // Hit login 6 times with wrong credentials; 6th should be 429
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/v1/auth/login', ['email' => 'x@x.com', 'password' => 'wrong'])
             ->assertStatus(422); // wrong credentials, but not throttled yet
    }

    $this->postJson('/api/v1/auth/login', ['email' => 'x@x.com', 'password' => 'wrong'])
         ->assertTooManyRequests();
});
