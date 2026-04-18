<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePrescriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasJobTitle('admin', 'doctor');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'patient_id' => 'sometimes|exists:patients,id',
            'medication_id' => 'sometimes|exists:medications,id',
            'prescriber_id' => 'sometimes|exists:users,id',
            'dosage' => 'sometimes|string',
            'frequency' => 'sometimes|string',
            'status' => 'sometimes|in:active,completed,stopped',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ];
    }
}
