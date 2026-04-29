<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrescriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient' => [
                'id' => $this->patient->id,
                'name' => $this->patient->name,
                'birthdate' => $this->patient->birthdate,
            ],

            'medication' => [
                'id' => $this->medication->id,
                'name' => $this->medication->name,
                'strength' => $this->medication->strength,
                'unit' => $this->medication->unit,
            ],

            'prescriber' => [
                'id' => $this->prescriber->id,
                'name' => $this->prescriber->name,
                'job_title' => $this->prescriber->job_title,
                'organization' => $this->prescriber->organization,
            ],


            'createdByUser' => [
                'id' => $this->createdByUser->id,
                'name' => $this->createdByUser->name,
                'job_title' => $this->createdByUser->job_title,
                'organization' => $this->createdByUser->organization,
            ],

            'nurse' => $this->nurse ? ['id' => $this->nurse->id, 'name' => $this->nurse->name] : null,
            'dosage' => $this->dosage,
            'frequency' => (int) $this->frequency,
            'status' => $this->status,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ];
    }
}
