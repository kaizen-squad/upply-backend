<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            "description" => $this->description,
            "budget" => $this->budget,
            "deadline" => $this->deadline,
            "status" => $this->status,
            "created_at" => $this->created_at,

            "client_id" => $this->client_id,
            "client" => new UserResource($this->whenLoaded('client')),
            "contract" => $this->whenLoaded('contract', fn() => [
                "application_id" => $this->contract->application_id
            ])
        ];
    }
}