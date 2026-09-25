<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class IsAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $tokenString = $request->bearerToken();

        if (! $tokenString) {
            return $this->unauthorized();
        }

        $token = PersonalAccessToken::findToken($tokenString);

        if (! $token) {
            return $this->unauthorized();
        }

        if ($token->cant('server:access')) {
            return $this->unauthorized();
        }

        if ($token && $token->expires_at < now()) {
            return $this->unauthorized();
        }

        $user = $token->tokenable;

        if (! $user) {
            return $this->unauthorized();
        }

        Auth::setUser($user);

        return $next($request);
    }

    private function unauthorized(): JsonResponse
    {
        return response()->json([
            'status' => 401,
            'success' => false,
            'code' => 'UNAUTHENTICATED',
            'message' => 'Authentication is required.',
            'data' => null,
        ], 401);
    }
}
