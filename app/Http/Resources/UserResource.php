<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
<<<<<<< HEAD
            'name' => $this->name,
            'email' => $this->email,
=======
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
>>>>>>> c723c54 (feat- register User)
            'role' => $this->role,
            'phone' => $this->phone,
            'rating_avg' => $this->rating_avg,
            'created_at' => $this->created_at
        ];
    }
}
