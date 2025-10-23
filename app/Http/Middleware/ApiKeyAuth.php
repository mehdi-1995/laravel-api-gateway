<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-KEY') ?? $request->header('API_KEY');
        if ($apiKey !== 'base64:FDiTrLSYPbNkEHKvbhqeGqv7WK0jq6DgHOy7zUhECoI=') { // در تولید، این رو از env بگیرید
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $next($request);
    }
}
