<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Role
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {

        $Urole = $request->user()->role;

        if ($Urole !== $role) {
            return response()->json([
                "success" => false,
                "code" => 403,
                "message" => "Forbidden"
            ], 403) ;
        }

        return $next($request);

    }
}
