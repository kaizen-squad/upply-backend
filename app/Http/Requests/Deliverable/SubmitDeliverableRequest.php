<?php

namespace App\Http\Requests\Deliverable;

use Illuminate\Foundation\Http\FormRequest;

class SubmitDeliverableRequest extends FormRequest
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
            'content' => ['string', 'required'],
            'file_path' => ['nullable', 'string'],

            'task_id' => ['required', 'exists:tasks,id']
        ];
    }
}
