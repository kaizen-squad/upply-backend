<?php

namespace App\Http\Requests\Task;

<<<<<<< HEAD
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
=======
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
>>>>>>> cacc6e1 (feat(tasks): Defined task storage DTO)

class TaskStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
<<<<<<< HEAD
        return true;
=======
        return false;
>>>>>>> cacc6e1 (feat(tasks): Defined task storage DTO)
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
<<<<<<< HEAD
            "title" => ['required', 'string'],
            "description" => ['required', 'string'],
            "budget" => ['required', 'numeric'],
            "deadline" => ['required', 'date_format:Y-m-d', 'after:today'],
            "status" => ['required', new Enum(TaskStatus::class)],

            "client_id" => ['required', 'exists:users,id']
=======
            //
>>>>>>> cacc6e1 (feat(tasks): Defined task storage DTO)
        ];
    }
}
