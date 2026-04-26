<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

class ReviewStoreRequest extends FormRequest
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
            "comment" => ["nullable", "string"],
            "rating" => ["required", "integer", "max_digits:1", "max:5"],

            "reviewer_id" => ["required", "exists:users,id"],
            "reviewee_id" => ["required", "exists:users,id", "different:reviewer_id"]
        ];
    }
}
