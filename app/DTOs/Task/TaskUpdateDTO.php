<?php

namespace App\DTOs\Task;

use App\Http\Requests\TaskUpdateRequest;
use Carbon\Carbon;

readonly class TaskUpdateDTO{
    public function __construct(
        public ?string $title,
        public ?string $description,
        public ?int $budget,
        public ?Carbon $deadline
    ){}

    public static function fromRequest(TaskUpdateRequest $request): self
    {
        $data = $request->validated();

        return new self(
            title: $data['title'],
            description: $data['description'],
            budget: $data['budget'],
            deadline: $data['deadline']
        );
    }
}