<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
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
            'message' => $this->message,

            'prestataire_id' => $this->prestataire_id,
            'task_id' => $this->task_id,
            'status' => $this->status,
            'created_at' => $this->created_at,

            'prestataire' => new UserResource($this->whenLoaded('prestataire')),
            'task' => $this->whenLoaded('task', fn() => [
                'id' => $this->task->id,
                'title' => $this->task->title,
                'description' => $this->task->description,
            ])
        ];
    }
}
