<?php

namespace App\DTOs\Application;

readonly class ApplicationListForTaskDTO{
    public function __construct(
        public string $task_id
    ){}

    public static function fromRequest(): self
    {
        return new self(
            task
        );
    }
}