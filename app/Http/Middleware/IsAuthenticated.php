<?php

namespace App\Http\Middleware;

use Closure;
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
        
        if(!$tokenString){
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized bro',
                'code' => 401
            ],401);
        }

        $token = PersonalAccessToken::findToken($tokenString);

        if( !$token ){
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'code' => 401
            ],401);
        }

        if($token->cant('server:access')){
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'code' => 401
            ],401);
        }

        if(  $token && $token->expires_at < now() ){
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'code' => 401
            ],401);
        }

    
        $user = $token->tokenable;
        Auth::login($user);

        return $next($request);
    }
}
