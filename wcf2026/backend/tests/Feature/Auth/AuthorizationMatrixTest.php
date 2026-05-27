<?php

it('rejects unauthenticated access to /auth/me', function () {
    $this->getJson('/api/v1/auth/me')->assertUnauthorized();
});

it('rejects unauthenticated access to /auth/logout', function () {
    $this->postJson('/api/v1/auth/logout')->assertUnauthorized();
});

it('rejects unauthenticated access to PATCH /auth/me', function () {
    $this->patchJson('/api/v1/auth/me', ['name' => 'Hacker'])->assertUnauthorized();
});

it('rejects unauthenticated access to POST /auth/password', function () {
    $this->postJson('/api/v1/auth/password', [])->assertUnauthorized();
});

it('rejects unauthenticated access to POST /auth/email/resend', function () {
    $this->postJson('/api/v1/auth/email/resend')->assertUnauthorized();
});

it('allows public access to POST /auth/register', function () {
    // Returns 422 (validation fails) not 401 — proves route is public
    $this->postJson('/api/v1/auth/register', [])->assertUnprocessable();
});

it('allows public access to POST /auth/password/forgot', function () {
    $this->postJson('/api/v1/auth/password/forgot', ['email' => 'x@x.com'])->assertNoContent();
});

it('allows public access to GET /api/v1/health', function () {
    $this->getJson('/api/v1/health')->assertOk();
});
