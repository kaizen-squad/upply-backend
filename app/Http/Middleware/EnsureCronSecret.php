<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCronSecret
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = env('CRON_SECRET');

        if (!$secret || $request->header('X-Cron-Secret') !== $secret) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'code'    => 401,
            ], 401);
        }

        return $next($request);
    }
}
