<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\Task;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTaskOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $user = $request->user();
        $task = $request->route('task');
        if(!$user){
            return response()->json([
                "success" => false,
                "message" => "Unauthorized",
                "code" => 401
            ],401); 
        }

        if ($user->role !== UserRole::Client){
            return response()->json([
                "success" => false,
                "message" => "Forbidden",
                "code" => 403
            ],403);
        }

        $Task = Task::where('id',$task)->first();

        if($user->id !== $Task->client_id ){
            return response()->json([
                "success" => false,
                "message" => "Forbidden",
                "code" => 403
            ],403);
        }

        return $next($request);
    }
}
