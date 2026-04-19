<?php

namespace App\Http\Requests\Task;

<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 470409b (feat(tasks): Defined task storage DTO)
=======
>>>>>>> 470409b (feat(tasks): Defined task storage DTO)
=======
>>>>>>> 470409b (feat(tasks): Defined task storage DTO)
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
=======
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
>>>>>>> f12918b (feat(tasks): Defined task storage DTO)
=======
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
>>>>>>> 59e257c (feat(tasks): Set up the validation request file for the task storage)
=======
>>>>>>> cacc6e1 (feat(tasks): Defined task storage DTO)
>>>>>>> 470409b (feat(tasks): Defined task storage DTO)
=======
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
>>>>>>> e6b67ca (chore: Ended rebase)
=======
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
>>>>>>> f12918b (feat(tasks): Defined task storage DTO)
=======
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
>>>>>>> 59e257c (feat(tasks): Set up the validation request file for the task storage)
=======
>>>>>>> cacc6e1 (feat(tasks): Defined task storage DTO)
>>>>>>> 470409b (feat(tasks): Defined task storage DTO)
=======
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
>>>>>>> e6b67ca (chore: Ended rebase)
=======
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
>>>>>>> f12918b (feat(tasks): Defined task storage DTO)
=======
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
>>>>>>> 59e257c (feat(tasks): Set up the validation request file for the task storage)
=======
>>>>>>> cacc6e1 (feat(tasks): Defined task storage DTO)
>>>>>>> 470409b (feat(tasks): Defined task storage DTO)
=======
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
>>>>>>> e6b67ca (chore: Ended rebase)

class TaskStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
        return true;
=======
        return false;
>>>>>>> f12918b (feat(tasks): Defined task storage DTO)
=======
        return true;
>>>>>>> 59e257c (feat(tasks): Set up the validation request file for the task storage)
=======
        return true;
=======
        return false;
>>>>>>> cacc6e1 (feat(tasks): Defined task storage DTO)
>>>>>>> 470409b (feat(tasks): Defined task storage DTO)
=======
        return true;
>>>>>>> e6b67ca (chore: Ended rebase)
=======
        return false;
>>>>>>> f12918b (feat(tasks): Defined task storage DTO)
=======
        return true;
>>>>>>> 59e257c (feat(tasks): Set up the validation request file for the task storage)
=======
        return true;
=======
        return false;
>>>>>>> cacc6e1 (feat(tasks): Defined task storage DTO)
>>>>>>> 470409b (feat(tasks): Defined task storage DTO)
=======
        return true;
>>>>>>> e6b67ca (chore: Ended rebase)
=======
        return false;
>>>>>>> f12918b (feat(tasks): Defined task storage DTO)
=======
        return true;
>>>>>>> 59e257c (feat(tasks): Set up the validation request file for the task storage)
=======
        return true;
=======
        return false;
>>>>>>> cacc6e1 (feat(tasks): Defined task storage DTO)
>>>>>>> 470409b (feat(tasks): Defined task storage DTO)
=======
        return true;
>>>>>>> e6b67ca (chore: Ended rebase)
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
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 59e257c (feat(tasks): Set up the validation request file for the task storage)
=======
>>>>>>> 470409b (feat(tasks): Defined task storage DTO)
=======
>>>>>>> e6b67ca (chore: Ended rebase)
=======
>>>>>>> 59e257c (feat(tasks): Set up the validation request file for the task storage)
=======
>>>>>>> 470409b (feat(tasks): Defined task storage DTO)
=======
>>>>>>> e6b67ca (chore: Ended rebase)
=======
>>>>>>> 59e257c (feat(tasks): Set up the validation request file for the task storage)
=======
>>>>>>> 470409b (feat(tasks): Defined task storage DTO)
=======
>>>>>>> e6b67ca (chore: Ended rebase)
            "title" => ['required', 'string'],
            "description" => ['required', 'string'],
            "budget" => ['required', 'numeric'],
            "deadline" => ['required', 'date_format:Y-m-d', 'after:today'],
            "status" => ['required', new Enum(TaskStatus::class)],

            "client_id" => ['required', 'exists:users,id']
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
=======
            //
>>>>>>> f12918b (feat(tasks): Defined task storage DTO)
=======
>>>>>>> 59e257c (feat(tasks): Set up the validation request file for the task storage)
=======
=======
            //
>>>>>>> cacc6e1 (feat(tasks): Defined task storage DTO)
>>>>>>> 470409b (feat(tasks): Defined task storage DTO)
=======
>>>>>>> e6b67ca (chore: Ended rebase)
=======
            //
>>>>>>> f12918b (feat(tasks): Defined task storage DTO)
=======
>>>>>>> 59e257c (feat(tasks): Set up the validation request file for the task storage)
=======
=======
            //
>>>>>>> cacc6e1 (feat(tasks): Defined task storage DTO)
>>>>>>> 470409b (feat(tasks): Defined task storage DTO)
=======
>>>>>>> e6b67ca (chore: Ended rebase)
=======
            //
>>>>>>> f12918b (feat(tasks): Defined task storage DTO)
=======
>>>>>>> 59e257c (feat(tasks): Set up the validation request file for the task storage)
=======
=======
            //
>>>>>>> cacc6e1 (feat(tasks): Defined task storage DTO)
>>>>>>> 470409b (feat(tasks): Defined task storage DTO)
=======
>>>>>>> e6b67ca (chore: Ended rebase)
        ];
    }
}
