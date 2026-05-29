<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $request->validate(['email' => ['required', 'email']]);

        // Always attempt; swallow "not found" to prevent user enumeration
        Password::sendResetLink($request->only('email'));

        return response()->noContent();
    }
}
