<?php

namespace App\Http\Controllers\Api\Deliverable;

use App\DTOs\Deliverable\SubmitDeliverableDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Deliverable\SubmitDeliverableRequest;
use App\Services\DeliverableService;

class DeliverableController extends Controller
{
    public function __construct(
        public DeliverableService $service
    ){}

    public function submit(SubmitDeliverableRequest $request){
        $user = $request->user();

        $data = SubmitDeliverableDTO::fromRequest($request);

        $response = $this->service->submit($user, $data);

        return response()->json([
            "success" => true,
            "data" => $response,
            "message" => "Deliverable submitted successfully."
        ], 200);
    }
}
