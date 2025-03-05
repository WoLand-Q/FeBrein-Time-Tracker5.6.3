<?php

namespace App\Http\Middleware;

use App\Enums\RoleEnum;
use Closure;
use Couchbase\Role;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if((int)auth()->user()->role_id->value != RoleEnum::Admin->value){
            abort(404);
        }

        return $next($request);
    }
}
