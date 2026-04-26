<?php

namespace App\Http\Controllers\Api\Deliverable;

use App\DTOs\Deliverable\SubmitDeliverableDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Deliverable\SubmitDeliverableRequest;
use App\Models\Deliverable;
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

    public function get(Deliverable $deliverable){
        $response = $this->service->get($deliverable);

        return response()->json([
            "success" => true,
            "data" => $response,
            "message" => "Deliverable fetched successfully"
        ], 200);
    }

    public function validate(Deliverable $deliverable){
        $response = $this->service->validate($deliverable);

        return response()->json([
            "success" => true,
            "data" => $response,
            "message" => "Deliverable validated successfully"
        ]);
    }
}
