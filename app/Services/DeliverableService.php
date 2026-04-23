<?php

namespace App\Services;

use App\DTOs\Deliverable\SubmitDeliverableDTO;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class DeliverableService{
    public function submit(User $prestataire, SubmitDeliverableDTO $data){
        // Check the ability to perform this action
        Gate::authorize('submit');

        
    }
}