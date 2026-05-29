<?php
declare(strict_types=1);

use App\Models\Tournament;
use App\Models\TournamentMembership;
use App\Models\User;

it('unauthenticated cannot create tournament', function () {
    $this->postJson('/api/v1/admin/tournaments', [
        'name' => 'T', 'slug' => 'tt', 'sport' => 'football',
    ])->assertUnauthorized();
});

it('unverified user cannot create tournament', function () {
    $admin = User::factory()->unverified()->create(['is_global_admin' => true]);
    $this->actingAs($admin)->postJson('/api/v1/admin/tournaments', [
        'name' => 'T', 'slug' => 'tt', 'sport' => 'football',
    ])->assertForbidden();
});

it('non-admin cannot create tournament', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->postJson('/api/v1/admin/tournaments', [
        'name' => 'T', 'slug' => 'tt', 'sport' => 'football',
    ])->assertForbidden();
});

it('global admin can create tournament', function () {
    $admin = User::factory()->create(['is_global_admin' => true]);
    $response = $this->actingAs($admin)->postJson('/api/v1/admin/tournaments', [
        'name'  => 'World Cup 2026',
        'slug'  => 'wc2026',
        'sport' => 'football',
    ]);
    $response->assertCreated();
    $response->assertJsonPath('data.slug', 'wc2026');
    $this->assertDatabaseHas('tournaments', ['slug' => 'wc2026', 'owner_user_id' => $admin->id]);
});

it('tournament create always defaults status to draft', function () {
    $admin = User::factory()->create(['is_global_admin' => true]);

    $this->actingAs($admin)->postJson('/api/v1/admin/tournaments', [
        'name' => 'Knockout Cup',
        'slug' => 'knockout-cup',
        'sport' => 'football',
        'status' => 'finished',
    ])->assertCreated()->assertJsonPath('data.status', 'draft');
});

it('slug must be unique', function () {
    $admin = User::factory()->create(['is_global_admin' => true]);
    Tournament::factory()->create(['slug' => 'duplicate']);
    $this->actingAs($admin)->postJson('/api/v1/admin/tournaments', [
        'name' => 'Dup', 'slug' => 'duplicate', 'sport' => 'football',
    ])->assertUnprocessable();
});

it('global admin can update tournament', function () {
    $admin = User::factory()->create(['is_global_admin' => true]);
    $t = Tournament::factory()->create();
    $this->actingAs($admin)->putJson("/api/v1/admin/tournaments/{$t->slug}", [
        'name' => 'Updated Name',
    ])->assertOk()->assertJsonPath('data.name', 'Updated Name');
});

it('tournament owner member can update tournament', function () {
    $user = User::factory()->create();
    $tournament = Tournament::factory()->create();
    TournamentMembership::factory()->asOwner()->create([
        'user_id' => $user->id,
        'tournament_id' => $tournament->id,
    ]);

    $this->actingAs($user)->putJson("/api/v1/admin/tournaments/{$tournament->slug}", [
        'name' => 'Owner Updated Name',
    ])->assertOk()->assertJsonPath('data.name', 'Owner Updated Name');
});

it('non-member cannot update tournament', function () {
    $user = User::factory()->create();
    $tournament = Tournament::factory()->create();

    $this->actingAs($user)->putJson("/api/v1/admin/tournaments/{$tournament->slug}", [
        'name' => 'Blocked Update',
    ])->assertForbidden();
});

it('global admin can delete tournament', function () {
    $admin = User::factory()->create(['is_global_admin' => true]);
    $t = Tournament::factory()->create();
    $this->actingAs($admin)->deleteJson("/api/v1/admin/tournaments/{$t->slug}")->assertNoContent();
    $this->assertSoftDeleted('tournaments', ['id' => $t->id]);
});
