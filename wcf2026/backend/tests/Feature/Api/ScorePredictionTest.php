<?php
declare(strict_types=1);

use App\Models\Fixture;
use App\Models\Round;
use App\Models\ScorePrediction;
use App\Models\Stage;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\User;

// ─── Helper ─────────────────────────────────────────────────────────────────

/** @return array{tournament:Tournament,stage:Stage,round:Round,home:Team,away:Team,fixture:Fixture} */
function makeTournamentWithFixture(string $status = 'scheduled', ?string $kickoffAt = null): array
{
    $tournament = Tournament::factory()->create(['status' => 'open']);
    $stage      = Stage::factory()->create(['tournament_id' => $tournament->id]);
    $round      = Round::factory()->create(['tournament_id' => $tournament->id, 'stage_id' => $stage->id]);
    $home       = Team::factory()->create(['tournament_id' => $tournament->id]);
    $away       = Team::factory()->create(['tournament_id' => $tournament->id]);
    $fixture    = Fixture::factory()->create([
        'tournament_id' => $tournament->id,
        'round_id'      => $round->id,
        'home_team_id'  => $home->id,
        'away_team_id'  => $away->id,
        'status'        => $status,
        'kickoff_at'    => $kickoffAt ?? now()->addDay()->toDateTimeString(),
    ]);

    return compact('tournament', 'stage', 'round', 'home', 'away', 'fixture');
}

// ─── GET /predictions/score ──────────────────────────────────────────────────

it('authenticated user can list their score predictions for a tournament', function () {
    ['tournament' => $tournament, 'fixture' => $fixture] = makeTournamentWithFixture();
    $user = User::factory()->create();

    ScorePrediction::factory()->create([
        'tournament_id' => $tournament->id,
        'user_id'       => $user->id,
        'fixture_id'    => $fixture->id,
        'home_score'    => 2,
        'away_score'    => 1,
    ]);

    $this->actingAs($user)
        ->getJson("/api/v1/tournaments/{$tournament->slug}/predictions/score")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.fixture_id', $fixture->id)
        ->assertJsonPath('data.0.home_score', 2)
        ->assertJsonPath('data.0.away_score', 1);
});

it('unauthenticated user cannot list predictions', function () {
    ['tournament' => $tournament] = makeTournamentWithFixture();

    $this->getJson("/api/v1/tournaments/{$tournament->slug}/predictions/score")
        ->assertUnauthorized();
});

it('unverified user cannot list predictions', function () {
    ['tournament' => $tournament] = makeTournamentWithFixture();
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->getJson("/api/v1/tournaments/{$tournament->slug}/predictions/score")
        ->assertForbidden();
});

it('returns only the authenticated user\'s own predictions', function () {
    ['tournament' => $tournament, 'fixture' => $fixture] = makeTournamentWithFixture();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    ScorePrediction::factory()->create([
        'tournament_id' => $tournament->id,
        'user_id'       => $user1->id,
        'fixture_id'    => $fixture->id,
    ]);

    $this->actingAs($user2)
        ->getJson("/api/v1/tournaments/{$tournament->slug}/predictions/score")
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

// ─── PUT /fixtures/{fixture}/prediction ─────────────────────────────────────

it('authenticated user can submit a score prediction', function () {
    ['tournament' => $tournament, 'fixture' => $fixture] = makeTournamentWithFixture();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->putJson("/api/v1/tournaments/{$tournament->slug}/fixtures/{$fixture->id}/prediction", [
            'home_score' => 2,
            'away_score' => 0,
        ])
        ->assertOk()
        ->assertJsonPath('data.home_score', 2)
        ->assertJsonPath('data.away_score', 0)
        ->assertJsonPath('data.fixture_id', $fixture->id);

    $this->assertDatabaseHas('score_predictions', [
        'user_id'    => $user->id,
        'fixture_id' => $fixture->id,
        'home_score' => 2,
        'away_score' => 0,
    ]);
});

it('authenticated user can update their existing prediction', function () {
    ['tournament' => $tournament, 'fixture' => $fixture] = makeTournamentWithFixture();
    $user = User::factory()->create();

    ScorePrediction::factory()->create([
        'tournament_id' => $tournament->id,
        'user_id'       => $user->id,
        'fixture_id'    => $fixture->id,
        'home_score'    => 1,
        'away_score'    => 1,
    ]);

    $this->actingAs($user)
        ->putJson("/api/v1/tournaments/{$tournament->slug}/fixtures/{$fixture->id}/prediction", [
            'home_score' => 3,
            'away_score' => 2,
        ])
        ->assertOk()
        ->assertJsonPath('data.home_score', 3)
        ->assertJsonPath('data.away_score', 2);

    expect(ScorePrediction::count())->toBe(1); // no duplicate created
});

it('unauthenticated user cannot submit a prediction', function () {
    ['tournament' => $tournament, 'fixture' => $fixture] = makeTournamentWithFixture();

    /** @var \Tests\TestCase $this */
    $this->putJson("/api/v1/tournaments/{$tournament->slug}/fixtures/{$fixture->id}/prediction", [
        'home_score' => 1,
        'away_score' => 0,
    ])->assertUnauthorized();
});

it('unverified user cannot submit a prediction', function () {
    ['tournament' => $tournament, 'fixture' => $fixture] = makeTournamentWithFixture();
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->putJson("/api/v1/tournaments/{$tournament->slug}/fixtures/{$fixture->id}/prediction", [
            'home_score' => 1,
            'away_score' => 0,
        ])->assertForbidden();
});

it('returns 423 when fixture kickoff has passed', function () {
    ['tournament' => $tournament, 'fixture' => $fixture] = makeTournamentWithFixture(
        kickoffAt: now()->subMinute()->toDateTimeString()
    );
    $user = User::factory()->create();

    $this->actingAs($user)
        ->putJson("/api/v1/tournaments/{$tournament->slug}/fixtures/{$fixture->id}/prediction", [
            'home_score' => 1,
            'away_score' => 0,
        ])->assertStatus(423);
});

it('returns 423 when fixture is live', function () {
    ['tournament' => $tournament, 'fixture' => $fixture] = makeTournamentWithFixture(status: 'live');
    $user = User::factory()->create();

    $this->actingAs($user)
        ->putJson("/api/v1/tournaments/{$tournament->slug}/fixtures/{$fixture->id}/prediction", [
            'home_score' => 1,
            'away_score' => 0,
        ])->assertStatus(423);
});

it('returns 423 when fixture is finished', function () {
    ['tournament' => $tournament, 'fixture' => $fixture] = makeTournamentWithFixture(status: 'finished');
    $user = User::factory()->create();

    $this->actingAs($user)
        ->putJson("/api/v1/tournaments/{$tournament->slug}/fixtures/{$fixture->id}/prediction", [
            'home_score' => 1,
            'away_score' => 0,
        ])->assertStatus(423);
});

it('validates home_score is required', function () {
    ['tournament' => $tournament, 'fixture' => $fixture] = makeTournamentWithFixture();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->putJson("/api/v1/tournaments/{$tournament->slug}/fixtures/{$fixture->id}/prediction", [
            'away_score' => 0,
        ])->assertUnprocessable()
        ->assertJsonValidationErrors(['home_score']);
});

it('validates away_score must be non-negative integer', function () {
    ['tournament' => $tournament, 'fixture' => $fixture] = makeTournamentWithFixture();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->putJson("/api/v1/tournaments/{$tournament->slug}/fixtures/{$fixture->id}/prediction", [
            'home_score' => 1,
            'away_score' => -1,
        ])->assertUnprocessable()
        ->assertJsonValidationErrors(['away_score']);
});

it('returns 404 when fixture does not belong to tournament', function () {
    $tournament1 = Tournament::factory()->create(['status' => 'open']);
    ['fixture' => $fixture] = makeTournamentWithFixture();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->putJson("/api/v1/tournaments/{$tournament1->slug}/fixtures/{$fixture->id}/prediction", [
            'home_score' => 1,
            'away_score' => 0,
        ])->assertNotFound();
});

it('predictions from other tournaments are not returned', function () {
    $user = User::factory()->create();

    // Tournament A with a prediction
    ['tournament' => $tA, 'fixture' => $fA] = makeTournamentWithFixture();
    ScorePrediction::factory()->create([
        'tournament_id' => $tA->id,
        'user_id'       => $user->id,
        'fixture_id'    => $fA->id,
    ]);

    // Tournament B — query here should return empty
    ['tournament' => $tB] = makeTournamentWithFixture();

    $this->actingAs($user)
        ->getJson("/api/v1/tournaments/{$tB->slug}/predictions/score")
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('can submit prediction with optional predicted_winner_team_id', function () {
    ['tournament' => $tournament, 'fixture' => $fixture, 'home' => $home] = makeTournamentWithFixture();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->putJson("/api/v1/tournaments/{$tournament->slug}/fixtures/{$fixture->id}/prediction", [
            'home_score'               => 1,
            'away_score'               => 1,
            'predicted_winner_team_id' => $home->id,
        ])
        ->assertOk()
        ->assertJsonPath('data.predicted_winner_team_id', $home->id);
});

it('validates home_score cannot exceed 99', function () {
    ['tournament' => $tournament, 'fixture' => $fixture] = makeTournamentWithFixture();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->putJson("/api/v1/tournaments/{$tournament->slug}/fixtures/{$fixture->id}/prediction", [
            'home_score' => 100,
            'away_score' => 0,
        ])->assertUnprocessable()
        ->assertJsonValidationErrors(['home_score']);
});

it('validates predicted_winner_team_id must exist in teams', function () {
    ['tournament' => $tournament, 'fixture' => $fixture] = makeTournamentWithFixture();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->putJson("/api/v1/tournaments/{$tournament->slug}/fixtures/{$fixture->id}/prediction", [
            'home_score'               => 1,
            'away_score'               => 1,
            'predicted_winner_team_id' => 999999,
        ])->assertUnprocessable()
        ->assertJsonValidationErrors(['predicted_winner_team_id']);
});
