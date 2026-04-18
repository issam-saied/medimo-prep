<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePrescriptionRequest extends FormRequest
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
            'patient_id' => 'required|exists:patients,id',
            'medication_id' => 'required|exists:medications,id',
            'prescriber_id' => 'required|exists:users,id',
            'dosage' => 'required|string',
            'frequency' => 'required|string',
            'status' => 'required|in:active,completed,stopped',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ];
    }
}
