<?php
declare(strict_types=1);

use App\Models\Team;
use App\Models\Tournament;
use App\Models\User;

it('admin can add team to tournament', function () {
    $admin = User::factory()->create(['is_global_admin' => true]);
    $t = Tournament::factory()->create();
    $this->actingAs($admin)
         ->postJson("/api/v1/admin/tournaments/{$t->slug}/teams", [
             'name' => 'Spain', 'short_name' => 'ESP',
         ])->assertCreated();
    $this->assertDatabaseHas('teams', ['tournament_id' => $t->id, 'name' => 'Spain']);
});

it('admin can update team', function () {
    $admin = User::factory()->create(['is_global_admin' => true]);
    $t = Tournament::factory()->create();
    $team = Team::factory()->create(['tournament_id' => $t->id]);
    $this->actingAs($admin)
         ->putJson("/api/v1/admin/tournaments/{$t->slug}/teams/{$team->id}", ['name' => 'Germany'])
         ->assertOk()->assertJsonPath('data.name', 'Germany');
});

it('admin can delete team', function () {
    $admin = User::factory()->create(['is_global_admin' => true]);
    $t = Tournament::factory()->create();
    $team = Team::factory()->create(['tournament_id' => $t->id]);
    $this->actingAs($admin)
         ->deleteJson("/api/v1/admin/tournaments/{$t->slug}/teams/{$team->id}")
         ->assertNoContent();
    $this->assertDatabaseMissing('teams', ['id' => $team->id]);
});

it('cannot update team belonging to different tournament (scopeBindings)', function () {
    $admin = User::factory()->create(['is_global_admin' => true]);
    $t1 = Tournament::factory()->create();
    $t2 = Tournament::factory()->create();
    $teamFromT2 = Team::factory()->create(['tournament_id' => $t2->id]);
    $this->actingAs($admin)
         ->putJson("/api/v1/admin/tournaments/{$t1->slug}/teams/{$teamFromT2->id}", ['name' => 'Hacked'])
         ->assertNotFound();
});
