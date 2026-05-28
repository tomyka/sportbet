<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;

it('all expected tables exist after migration', function () {
    foreach ([
        'tournaments', 'tournament_memberships', 'stages',
        'groups', 'rounds', 'teams', 'team_stage_entries', 'fixtures', 'score_predictions',
    ] as $table) {
        expect(Schema::hasTable($table))->toBeTrue("Table [{$table}] is missing");
    }
});

it('tournaments table has expected columns', function () {
    expect(Schema::hasColumns('tournaments', [
        'id', 'name', 'slug', 'sport', 'status', 'owner_user_id',
        'starts_at', 'ends_at', 'settings', 'created_at', 'updated_at', 'deleted_at',
    ]))->toBeTrue();
});

it('fixtures table has score columns', function () {
    expect(Schema::hasColumns('fixtures', [
        'home_score', 'away_score', 'home_score_et', 'away_score_et',
        'home_score_pen', 'away_score_pen', 'winner_team_id', 'neutral_venue',
    ]))->toBeTrue();
});

it('score_predictions table has expected columns', function () {
    expect(Schema::hasColumns('score_predictions', [
        'id', 'tournament_id', 'user_id', 'fixture_id',
        'home_score', 'away_score', 'predicted_winner_team_id', 'submitted_at',
        'created_at', 'updated_at',
    ]))->toBeTrue();
});
