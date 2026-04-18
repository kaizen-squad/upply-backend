<?php

namespace App\Http\Resources;

use Illuminate\Database\Eloquent\Attributes\UseResource;
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

            "client" => new UseResource($this->whenLoaded('client'))
        ];
    }
}
