<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\Application;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePrestataire
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $user = request()->user();
        $task = request()->route('task');
        if($user->role !== UserRole::Prestataire){
            return response()->json([
                "success" => false,
                "message" => "Forbidden",
                "code" => 403
            ],403);
        }

        $application = Application::where([
            'task_id' => $task,
            'prestataire_id' => $user->id
        ]);

        if(!$application){
            return response()->json([
                    "success" => false,
                    "message" => "Forbidden",
                    "code" => 403
                ],403);
        }

        return $next($request);
    }
}
