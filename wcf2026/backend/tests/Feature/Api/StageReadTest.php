<?php
declare(strict_types=1);

use App\Models\Stage;
use App\Models\Tournament;
use App\Models\TournamentMembership;
use App\Models\User;

it('returns stages for open tournament', function () {
    $t = Tournament::factory()->create(['status' => 'open']);
    Stage::factory()->count(3)->create(['tournament_id' => $t->id]);

    $this->getJson("/api/v1/tournaments/{$t->slug}/stages")
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('returns 404 for stages of draft tournament (unauthenticated)', function () {
    $t = Tournament::factory()->draft()->create();
    Stage::factory()->create(['tournament_id' => $t->id]);
    $this->getJson("/api/v1/tournaments/{$t->slug}/stages")->assertNotFound();
});

it('allows global admin to see stages for a draft tournament', function () {
    $t = Tournament::factory()->draft()->create();
    Stage::factory()->count(2)->create(['tournament_id' => $t->id]);
    $admin = User::factory()->create(['is_global_admin' => true]);

    $this->actingAs($admin)
        ->getJson("/api/v1/tournaments/{$t->slug}/stages")
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('allows tournament admin member to see stages for a draft tournament', function () {
    $t = Tournament::factory()->draft()->create();
    Stage::factory()->count(2)->create(['tournament_id' => $t->id]);
    $user = User::factory()->create();
    TournamentMembership::factory()->create([
        'user_id' => $user->id,
        'tournament_id' => $t->id,
        'role' => 'admin',
    ]);

    $this->actingAs($user)
        ->getJson("/api/v1/tournaments/{$t->slug}/stages")
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('returns 404 for player member when requesting stages for a draft tournament', function () {
    $t = Tournament::factory()->draft()->create();
    Stage::factory()->create(['tournament_id' => $t->id]);
    $user = User::factory()->create();
    TournamentMembership::factory()->create([
        'user_id' => $user->id,
        'tournament_id' => $t->id,
        'role' => 'player',
    ]);

    $this->actingAs($user)
        ->getJson("/api/v1/tournaments/{$t->slug}/stages")
        ->assertNotFound();
});
