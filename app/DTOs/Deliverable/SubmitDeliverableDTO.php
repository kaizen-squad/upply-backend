<?php

namespace App\DTOs\Deliverable;

use App\Http\Requests\Deliverable\SubmitDeliverableRequest;

readonly class SubmitDeliverableDTO{
    public function __construct(
        public string $content,
        public ?string $file_path,
        public string $task_id
    ){}

    public static function fromRequest(SubmitDeliverableRequest $request): self
    {
        $data = $request->validated();

        return new self(
            content: $data['content'],
            file_path: $data['file_path'],
            task_id: $data['task_id']
        );
    }
}