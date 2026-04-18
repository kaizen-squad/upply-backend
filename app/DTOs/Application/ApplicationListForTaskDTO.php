<?php

namespace App\DTOs\Application;

use App\Http\Requests\Application\ApplicationListForTaskRequest;

readonly class ApplicationListForTaskDTO{
    public function __construct(
        public string $task_id
    ){}

    public static function fromRequest(ApplicationListForTaskRequest $request): self
    {
        $data = $request->validated();

        return new self(
            task_id: $data['task_id']
        );
    }
}