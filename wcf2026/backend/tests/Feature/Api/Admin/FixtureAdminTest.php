<?php
declare(strict_types=1);

use App\Models\Fixture;
use App\Models\Round;
use App\Models\Stage;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\User;

it('admin can create fixture', function () {
    $admin = User::factory()->create(['is_global_admin' => true]);
    $t = Tournament::factory()->create();
    $stage = Stage::factory()->create(['tournament_id' => $t->id]);
    $round = Round::factory()->create(['tournament_id' => $t->id, 'stage_id' => $stage->id]);
    $home = Team::factory()->create(['tournament_id' => $t->id]);
    $away = Team::factory()->create(['tournament_id' => $t->id]);

    $this->actingAs($admin)
         ->postJson("/api/v1/admin/tournaments/{$t->slug}/fixtures", [
             'round_id'     => $round->id,
             'home_team_id' => $home->id,
             'away_team_id' => $away->id,
             'kickoff_at'   => '2026-06-10T20:00:00Z',
         ])->assertCreated();
});

it('admin can update fixture scores', function () {
    $admin = User::factory()->create(['is_global_admin' => true]);
    $t = Tournament::factory()->create();
    $fixture = Fixture::factory()->create(['tournament_id' => $t->id]);
    $this->actingAs($admin)
         ->putJson("/api/v1/admin/tournaments/{$t->slug}/fixtures/{$fixture->id}", [
             'status' => 'finished', 'home_score' => 2, 'away_score' => 1,
         ])->assertOk()->assertJsonPath('data.home_score', 2);
});

it('admin can delete fixture', function () {
    $admin = User::factory()->create(['is_global_admin' => true]);
    $t = Tournament::factory()->create();
    $fixture = Fixture::factory()->create(['tournament_id' => $t->id]);
    $this->actingAs($admin)
         ->deleteJson("/api/v1/admin/tournaments/{$t->slug}/fixtures/{$fixture->id}")
         ->assertNoContent();
    $this->assertDatabaseMissing('fixtures', ['id' => $fixture->id]);
});

it('cannot create fixture with related models from a different tournament', function () {
    $admin = User::factory()->create(['is_global_admin' => true]);
    $t1 = Tournament::factory()->create();
    $t2 = Tournament::factory()->create();
    $stage = Stage::factory()->create(['tournament_id' => $t2->id]);
    $round = Round::factory()->create(['tournament_id' => $t2->id, 'stage_id' => $stage->id]);
    $home = Team::factory()->create(['tournament_id' => $t2->id]);
    $away = Team::factory()->create(['tournament_id' => $t2->id]);

    $this->actingAs($admin)
         ->postJson("/api/v1/admin/tournaments/{$t1->slug}/fixtures", [
             'round_id' => $round->id,
             'home_team_id' => $home->id,
             'away_team_id' => $away->id,
         ])->assertUnprocessable();
});

it('cannot assign a winner from a different tournament', function () {
    $admin = User::factory()->create(['is_global_admin' => true]);
    $t1 = Tournament::factory()->create();
    $t2 = Tournament::factory()->create();
    $fixture = Fixture::factory()->create(['tournament_id' => $t1->id]);
    $winner = Team::factory()->create(['tournament_id' => $t2->id]);

    $this->actingAs($admin)
         ->putJson("/api/v1/admin/tournaments/{$t1->slug}/fixtures/{$fixture->id}", [
             'winner_team_id' => $winner->id,
         ])->assertUnprocessable();
});
