<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliverableResource extends JsonResource
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
            'content' => $this->content,
            'task_id' => $this->task_id,
            
            'file' => [
                "file_url" => $this->file_url,
                'file_name' => $this->file_name,
                "file_size" => $this->file_size,
                "file_type" => $this->file_type,
            ],

            'submitted_at' => $this->submitted_at,

            'prestataire' => $this->whenLoaded('prestataire', fn() => [
                'name' => $this->prestataire->name,
                'rating_avg' => $this->prestataire->rating_avg
            ]),
            'task' => new TaskResource($this->whenLoaded('task'))
        ];
    }
}
