<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SessionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, remember: true)) {
            throw ValidationException::withMessages(['email' => 'Invalid credentials.']);
        }

        $request->session()->regenerate();

        $user = $request->user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        AuditLogger::record(
            action: 'user.login',
            actorUserId: $user->id,
            subjectType: 'user',
            subjectId: $user->id,
            ip: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return response()->json(['data' => [
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'email_verified' => $user->hasVerifiedEmail(),
        ]]);
    }

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        return response()->json(['data' => [
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'display_name' => $user->display_name,
            'time_zone' => $user->time_zone,
            'locale' => $user->locale,
            'email_verified' => $user->hasVerifiedEmail(),
            'is_global_admin' => $user->is_global_admin,
        ]]);
    }

    public function destroy(Request $request): Response
    {
        $actorId = $request->user()?->id;

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        try {
            AuditLogger::record(
                action: 'user.logout',
                actorUserId: $actorId,
                ip: $request->ip(),
                userAgent: $request->userAgent(),
            );
        } catch (\Throwable) {
            // Audit failure must never prevent logout
        }

        return response()->noContent();
    }
}
