<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * @method middleware(string $class)
 */
class AuthenticatedSessionController extends Controller
{
    public function store(LoginRequest $request): JsonResponse
    {

        $request->authenticate();

        $request->session()->regenerate();

        return response()->json(Auth::user()->role_id->value);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
