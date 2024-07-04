<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckAdmin
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (Auth::user()->role_id !== 1) {
            return response()->json(['error' => 'No tienes permisos para realizar esta acción.'], 403);
        }

        return $next($request);
    }
}
