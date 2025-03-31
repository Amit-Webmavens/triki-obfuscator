<?php

namespace WebMavens\Triki\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $validKey = config('triki.auth_key');

        if (session('triki_authenticated')) {
            return $next($request);
        }

        $providedKey = $request->query('auth_key');

        if (!$providedKey || $providedKey !== $validKey) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        session(['triki_authenticated' => true]);

        return $next($request);
    }
}
