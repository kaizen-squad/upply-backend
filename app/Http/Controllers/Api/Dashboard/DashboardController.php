<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        public DashboardService $service
    ){}

    public function forClient(Request $request){
        $user = $request->user();
        
        $response = $this->service->forClient($user);

        return response()->json([
            "success" => true,
            "data" => $response,
            "message" => "There is the aggregations for the client."
        ]);
    }
}
