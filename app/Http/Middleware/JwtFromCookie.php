<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtFromCookie
{
    public function handle(Request $request, Closure $next)
    {
       
    Log::info('Headers: ' . json_encode($request->headers->all()));
    Log::info('Cookies: ' . json_encode($request->cookies->all()));
        if ($request->hasCookie('jwt_token')) {
            $token = $request->cookie('jwt_token');
            $request->headers->set('Authorization', 'Bearer ' . $token);
    
            try {
                $user = JWTAuth::parseToken()->authenticate();
                if (!$user) {
                    return response()->json(['error' => 'User not found'], 404);
                }
            } catch (\Exception $e) {
                return response()->json(['error' => 'Token is not valid'], 401);
            }
        } 
        else {
            Log::info('Token not found in request');
            return response()->json(['error' => 'Token not found'], 401);
        }
    
        return $next($request);
    }
    
    
}
