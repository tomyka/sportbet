<?php
declare(strict_types=1);

use App\Models\Tournament;
use App\Models\User;
use App\Models\Stage;

it('admin can create group for tournament stage', function () {
    $admin = User::factory()->create(['is_global_admin' => true]);
    $t = Tournament::factory()->create();
    $stage = Stage::factory()->create(['tournament_id' => $t->id]);

    $this->actingAs($admin)
        ->postJson("/api/v1/admin/tournaments/{$t->slug}/groups", [
            'name' => 'Group A',
            'stage_id' => $stage->id,
        ])->assertCreated();

    $this->assertDatabaseHas('groups', [
        'tournament_id' => $t->id,
        'stage_id' => $stage->id,
        'name' => 'Group A',
    ]);
});

it('cannot create group with a stage from a different tournament', function () {
    $admin = User::factory()->create(['is_global_admin' => true]);
    $t1 = Tournament::factory()->create();
    $t2 = Tournament::factory()->create();
    $foreignStage = Stage::factory()->create(['tournament_id' => $t2->id]);

    $this->actingAs($admin)
        ->postJson("/api/v1/admin/tournaments/{$t1->slug}/groups", [
            'name' => 'Group A',
            'stage_id' => $foreignStage->id,
        ])->assertUnprocessable();

    $this->assertDatabaseMissing('groups', [
        'tournament_id' => $t1->id,
        'stage_id' => $foreignStage->id,
        'name' => 'Group A',
    ]);
});
