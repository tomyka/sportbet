<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmailVerificationController extends Controller
{
    public function verify(EmailVerificationRequest $request): Response
    {
        $user = $request->user();
        assert($user !== null);

        if (! $user->hasVerifiedEmail()) {
            $request->fulfill(); // marks verified + fires Verified event
        }

        return response()->noContent();
    }

    public function resend(Request $request): Response
    {
        $user = $request->user();
        assert($user !== null);

        if (! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        return response()->noContent();
    }
}
