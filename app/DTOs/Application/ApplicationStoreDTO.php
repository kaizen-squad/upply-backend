<?php

namespace App\DTOs\Application;

use App\Http\Requests\Application\ApplicationStoreRequest;

readonly class ApplicationStoreDTO{
    public function __construct(
        public string $message,
        public string $task_id
    ){}

    public static function fromRequest(ApplicationStoreRequest $request): self
    {
        $data = $request->validated();

        return new self(
            message: $data['message'],
            task_id: $data['task_id']
        );
    }
}