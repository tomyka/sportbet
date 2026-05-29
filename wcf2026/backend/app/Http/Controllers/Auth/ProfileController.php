<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateProfileRequest;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    public function __invoke(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        $user->update($request->validated());

        return response()->json(['data' => [
            'id' => $user->id,
            'name' => $user->name,
            'display_name' => $user->display_name,
            'email' => $user->email,
            'time_zone' => $user->time_zone,
            'locale' => $user->locale,
            'email_verified' => $user->hasVerifiedEmail(),
        ]]);
    }
}
