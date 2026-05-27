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
        if (! $user) {
            abort(401);
        }

        $user->update(['password' => $request->string('password')->value()]);

        AuditLogger::record(
            action: 'user.password_changed',
            actorUserId: $user->id,
            subjectType: 'user',
            subjectId: $user->id,
            ip: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return response()->noContent();
    }
}
