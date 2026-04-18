<?php

namespace App\DTOs\Task;

use App\Http\Requests\Task\TaskStoreRequest;
use Carbon\Carbon;

readonly class TaskStoreDTO{
    public function __construct(
        public string $title,
        public string $description,
        public int $budget,
        public Carbon $deadline,
    ){}

    public static function fromRequest(TaskStoreRequest $request): self
    {
        $data = $request->validated();

        return new self(
            title: $data['title'],
            description: $data['description'],
            budget: $data['budget'],
            deadline: Carbon::parse($data['deadline'])
        );
    }
}