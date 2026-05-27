<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->string('name')->value(),
            'email' => $request->string('email')->value(),
            'password' => $request->string('password')->value(),
        ]);

        event(new Registered($user));

        AuditLogger::record(
            action: 'user.register',
            actorUserId: null,
            subjectType: 'user',
            subjectId: $user->id,
            ip: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified' => $user->hasVerifiedEmail(),
            ],
        ], 201);
    }
}
