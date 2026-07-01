<?php

namespace App\Http\Controllers\Api\Queue;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class QueueWorkerController
{
    public function process(): JsonResponse
    {
        Artisan::call('queue:work', [
            '--stop-when-empty' => true,
            '--tries'           => 3,
            '--timeout'         => 55,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Queue processed',
        ]);
    }
}
