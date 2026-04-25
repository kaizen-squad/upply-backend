<?php

namespace App\DTOs\Task;

use App\Http\Requests\Task\TaskUpdateRequest;
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
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            budget: $data['budget'] ?? null,
            deadline: isset($data['deadline']) ? Carbon::parse($data['deadline']) : null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'description' => $this->description,
            'budget' => $this->budget,
            'deadline' => $this->deadline?->format('Y-m-d'),
        ], fn($value) => !is_null($value));
    }
}