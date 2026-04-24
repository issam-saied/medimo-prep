<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePrescriptionRequest extends FormRequest
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
            'patient_id' => 'required|exists:patients,id',
            'medication_id' => 'required|exists:medications,id',
            'prescriber_id' => ['required', Rule::exists('users', 'id')->where('role', 'doctor')],
            'dosage' => 'required|string',
            'frequency' => 'required|integer|min:1|max:24',
            'status' => 'required|in:active,completed,stopped',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ];
    }
}
