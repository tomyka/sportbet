<?php
declare(strict_types=1);

use App\Models\Fixture;

it('fixture has required fillable columns', function () {
    $fillable = (new Fixture())->getFillable();
    foreach (['tournament_id', 'round_id', 'home_team_id', 'away_team_id', 'status', 'home_score', 'away_score'] as $col) {
        expect($fillable)->toContain($col);
    }
});

it('fixture factory creates related models in the same tournament', function () {
    $fixture = Fixture::factory()->create();
    $round = $fixture->round()->firstOrFail();
    $homeTeam = $fixture->homeTeam()->firstOrFail();
    $awayTeam = $fixture->awayTeam()->firstOrFail();

    expect($fixture->tournament_id)->toBe($round->tournament_id)
        ->and($fixture->tournament_id)->toBe($homeTeam->tournament_id)
        ->and($fixture->tournament_id)->toBe($awayTeam->tournament_id);
});
