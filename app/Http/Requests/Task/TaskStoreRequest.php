<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class TaskStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "title" => ['required', 'string'],
            "description" => ['required', 'string'],
            "budget" => ['required', 'numeric'],
            "deadline" => ['required', 'date_format:Y-m-d', 'after:today'],
            "status" => ['required', new Enum(TaskStatus::class)],
        ];
    }
}
