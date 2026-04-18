<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListAdministrationRequest extends FormRequest
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
            'sort_field' => ['nullable', 'in:administered_at,patient,medication,prescriber,status'],
            'status' => ['nullable', Rule::in(['given', 'missed', 'refused'])],
            'patient_name' => ['nullable', 'string', 'max:255'],
            'medication_name' => ['nullable', 'string', 'max:255'],
            'prescriber_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
