<?php

namespace App\DTOs\Task;

use App\Enums\TaskStatus;
use App\Http\Requests\Task\TaskStoreRequest;
use Carbon\Carbon;

readonly class TaskStoreDTO{
    public function __construct(
        public string $client_id,
        public string $title,
        public string $description,
        public int $budget,
        public Carbon $deadline,
        public TaskStatus $status
    ){}

    public static function fromRequest(TaskStoreRequest $request): self
    {
        $data = $request->validated();

        return new self(
            client_id: $data['client_id'],
            title: $data['title'],
            description: $data['description'],
            budget: $data['budget'],
            deadline: Carbon::parse($data['deadline']),
            status: TaskStatus::from($data['status'])
        );
    }
}