<?php

it('allows the SPA origin with credentials', function () {
    $response = $this->withHeaders([
        'Origin' => 'http://app.lvh.me:5173',
    ])->getJson('/api/v1/health');

    $response->assertOk();
    expect($response->headers->get('Access-Control-Allow-Origin'))->toBe('http://app.lvh.me:5173');
    expect($response->headers->get('Access-Control-Allow-Credentials'))->toBe('true');
});
