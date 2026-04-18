<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAdministrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasJobTitle('admin', 'nurse');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'prescription_id' => 'required|exists:prescriptions,id',
            'status' => 'required|in:given,missed,refused',
            'administered_at' => 'required|date',
            'note' => 'nullable|string|required_if:status,missed,refused',
        ];
    }
}
