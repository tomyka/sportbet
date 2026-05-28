<?php
declare(strict_types=1);

use App\Models\Round;

it('round factory creates a stage in the same tournament', function () {
    $round = Round::factory()->create();
    $stage = $round->stage()->firstOrFail();

    expect($round->tournament_id)->toBe($stage->tournament_id);
});
