<?php

namespace App\DTOs\Deliverable;

readonly class SubmitDeliverableDTO{
    public function __construct(
        public string $content,
        public ?string $file_path,
        public string $task_id
    ){}

    public static function fromRequest(){
        //
    }
}