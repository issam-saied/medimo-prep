<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ListPrescriptionRequest extends FormRequest
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
            'sort_field' => ['nullable', 'in:start_date,end_date,patient,medication,prescriber,status'],
            'status' => ['nullable', 'in:active,completed,stopped'],
            'patient_name' => ['nullable', 'string', 'max:255'],
            'medication_name' => ['nullable', 'string', 'max:255'],
            'prescriber_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
