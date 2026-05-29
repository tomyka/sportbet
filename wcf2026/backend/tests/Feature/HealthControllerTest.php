<?php

use function Pest\Laravel\getJson;

it('returns 200 with status ok and db check', function () {
    getJson('/api/v1/health')
        ->assertOk()
        ->assertJsonStructure(['status', 'checks' => ['database']])
        ->assertJsonPath('status', 'ok')
        ->assertJsonPath('checks.database', 'ok');
});
