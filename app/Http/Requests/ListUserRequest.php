<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ListUserRequest extends FormRequest
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
            'sort_direction' => ['nullable', 'in:asc,desc'],
            'sort_field' => ['nullable', 'in:id,name,email,job_title,organization'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
        ];
    }
}
