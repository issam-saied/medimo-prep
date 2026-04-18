<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ListMedicationRequest extends FormRequest
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
            'sort_field' => ['nullable', 'in:id,name,form'],
            'name' => ['nullable', 'string', 'max:255'],
            'form' => ['nullable', 'string', 'max:255'],
        ];
    }
}
