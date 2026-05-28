<?php
declare(strict_types=1);

use App\Models\Tournament;
use App\Models\TournamentMembership;
use App\Models\User;

it('lists only non-draft tournaments when not authenticated', function () {
    Tournament::factory()->count(2)->create(['status' => 'open']);
    Tournament::factory()->draft()->create();

    $response = $this->getJson('/api/v1/tournaments');

    $response->assertOk();
    $response->assertJsonCount(2, 'data');
});

it('shows a public tournament without auth', function () {
    $t = Tournament::factory()->create(['status' => 'open']);
    $this->getJson("/api/v1/tournaments/{$t->slug}")->assertOk();
});

it('returns 404 for draft tournament when not authenticated', function () {
    $t = Tournament::factory()->draft()->create();
    $this->getJson("/api/v1/tournaments/{$t->slug}")->assertNotFound();
});

it('returns 404 for draft tournament for unprivileged user', function () {
    $t = Tournament::factory()->draft()->create();
    $user = User::factory()->create();
    $this->actingAs($user)->getJson("/api/v1/tournaments/{$t->slug}")->assertNotFound();
});

it('allows global admin to see draft tournament', function () {
    $t = Tournament::factory()->draft()->create();
    $admin = User::factory()->create(['is_global_admin' => true]);
    $this->actingAs($admin)->getJson("/api/v1/tournaments/{$t->slug}")->assertOk();
});

it('allows tournament admin member to see draft tournament', function () {
    $t = Tournament::factory()->draft()->create();
    $user = User::factory()->create();
    TournamentMembership::factory()->create(['user_id' => $user->id, 'tournament_id' => $t->id, 'role' => 'admin']);
    $this->actingAs($user)->getJson("/api/v1/tournaments/{$t->slug}")->assertOk();
});

it('allows tournament owner member to see draft tournament', function () {
    $t = Tournament::factory()->draft()->create();
    $user = User::factory()->create();
    TournamentMembership::factory()->create(['user_id' => $user->id, 'tournament_id' => $t->id, 'role' => 'owner']);
    $this->actingAs($user)->getJson("/api/v1/tournaments/{$t->slug}")->assertOk();
});

it('does NOT allow player member to see draft tournament', function () {
    $t = Tournament::factory()->draft()->create();
    $user = User::factory()->create();
    TournamentMembership::factory()->create(['user_id' => $user->id, 'tournament_id' => $t->id, 'role' => 'player']);
    $this->actingAs($user)->getJson("/api/v1/tournaments/{$t->slug}")->assertNotFound();
});

it('stages are hidden for draft tournament when unauthenticated', function () {
    $t = Tournament::factory()->draft()->create();
    $this->getJson("/api/v1/tournaments/{$t->slug}/stages")->assertNotFound();
});

it('teams are hidden for draft tournament when unauthenticated', function () {
    $t = Tournament::factory()->draft()->create();
    $this->getJson("/api/v1/tournaments/{$t->slug}/teams")->assertNotFound();
});

it('fixtures are hidden for draft tournament when unauthenticated', function () {
    $t = Tournament::factory()->draft()->create();
    $this->getJson("/api/v1/tournaments/{$t->slug}/fixtures")->assertNotFound();
});
