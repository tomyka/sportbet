<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Services\AuditLogger;
use Illuminate\Http\Response;

class PasswordController extends Controller
{
    public function __invoke(ChangePasswordRequest $request): Response
    {
        $user = $request->user();
        assert($user !== null);

        $user->update(['password' => $request->string('password')->value()]);

        try {
            AuditLogger::record(
                action: 'user.password_changed',
                actorUserId: $user->id,
                subjectType: 'user',
                subjectId: $user->id,
                ip: $request->ip(),
                userAgent: $request->userAgent(),
            );
        } catch (\Throwable) {
            // Audit failure must never prevent password change response
        }

        return response()->noContent();
    }
}
