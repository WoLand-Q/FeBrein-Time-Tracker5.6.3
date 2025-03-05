<?php

namespace App\Http\Middleware;

use App\Enums\RoleEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** @property RoleEnum role_id */
class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        dd($roles);
        $role = RoleEnum::from((int)auth()->user()->role_id->value);
        if (!in_array($role->label(), $roles)) {
            return response()->json(['message' => 'Not role!'], 403);
        }

        return $next($request);
    }
}
