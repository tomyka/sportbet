<?php
declare(strict_types=1);

use App\Models\Fixture;
use App\Models\ScorePrediction;
use App\Models\Tournament;
use App\Models\User;

it('score prediction belongs to a user', function () {
    $prediction = new ScorePrediction();
    expect($prediction->user())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
});

it('score prediction belongs to a fixture', function () {
    $prediction = new ScorePrediction();
    expect($prediction->fixture())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
});

it('score prediction belongs to a tournament', function () {
    $prediction = new ScorePrediction();
    expect($prediction->tournament())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
});

it('home_score and away_score are cast to int', function () {
    $p = new ScorePrediction(['home_score' => '2', 'away_score' => '1']);
    expect($p->home_score)->toBe(2)
        ->and($p->away_score)->toBe(1);
});

it('submitted_at is cast to datetime', function () {
    $p = new ScorePrediction(['submitted_at' => '2026-06-01 18:00:00']);
    expect($p->submitted_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

it('predicted_winner_team_id is fillable and nullable', function () {
    $p = new ScorePrediction(['predicted_winner_team_id' => null]);
    expect($p->getFillable())->toContain('predicted_winner_team_id')
        ->and($p->predicted_winner_team_id)->toBeNull();
});

it('factory creates fixture in the same tournament as the prediction', function () {
    $prediction = ScorePrediction::factory()->create();
    $fixture = Fixture::query()->findOrFail($prediction->fixture_id);

    expect($fixture->tournament_id)->toBe($prediction->tournament_id);
});

it('is_global_admin is not fillable', function () {
    $prediction = new ScorePrediction();
    expect($prediction->getFillable())->not->toContain('is_global_admin');
});
