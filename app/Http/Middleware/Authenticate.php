<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

use Closure;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }

    public function handle($request, Closure $next, ...$guards)
    {
        // Check for token in cookie or Authorization header
        $token = $request->cookie('token') ?? $this->extractBearerToken($request);

        if ($token) {
            // Set the Authorization header for proper token-based authentication
            $request->headers->set('Authorization', 'Bearer ' . $token);
        } else {
            return response()->json([
                'message' => 'unauthorized'
            ], 401);
        }

        // Ensure the user is authenticated
        $this->authenticate($request, $guards);

        return $next($request);
    }

    /**
     * Extract Bearer Token from the Authorization header.
     */
    private function extractBearerToken(Request $request): ?string
    {
        $authorizationHeader = $request->header('Authorization');
        if ($authorizationHeader && preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
