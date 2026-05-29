<?php

use App\Models\AuditEvent;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('records an audit event with all fields', function () {
    $user = User::factory()->create();

    AuditLogger::record(
        action: 'user.login',
        actorUserId: $user->id,
        subjectType: 'user',
        subjectId: $user->id,
        changes: ['ip' => '1.2.3.4'],
        ip: '1.2.3.4',
        userAgent: 'Test/1.0',
    );

    expect(AuditEvent::count())->toBe(1);
    $event = AuditEvent::first();
    assert($event !== null);
    expect($event->action)->toBe('user.login')
        ->and($event->actor_user_id)->toBe($user->id)
        ->and($event->subject_type)->toBe('user')
        ->and($event->subject_id)->toBe($user->id)
        ->and($event->ip)->toBe('1.2.3.4');
});

it('allows null actor for anonymous events', function () {
    AuditLogger::record(action: 'user.register', ip: '1.2.3.4');

    expect(AuditEvent::count())->toBe(1);
    $event = AuditEvent::first();
    assert($event !== null);
    expect($event->actor_user_id)->toBeNull();
});
