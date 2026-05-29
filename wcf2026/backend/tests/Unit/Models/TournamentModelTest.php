<?php
declare(strict_types=1);

use App\Models\Tournament;

it('tournament has slug fillable', function () {
    $t = new Tournament(['name' => 'Test', 'slug' => 'test', 'sport' => 'football', 'owner_user_id' => 1]);
    expect($t->slug)->toBe('test');
});

it('tournament is_global_admin is not fillable', function () {
    expect((new Tournament())->getFillable())->not->toContain('is_global_admin');
});

it('tournament isDraft returns true when status is draft', function () {
    $t = new Tournament(['status' => 'draft']);
    expect($t->isDraft())->toBeTrue();
    $t->status = 'open';
    expect($t->isDraft())->toBeFalse();
});

it('tournament softDeletes trait present', function () {
    expect(in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive(Tournament::class)))->toBeTrue();
});
