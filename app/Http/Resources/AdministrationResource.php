<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdministrationResource extends JsonResource
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
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'job_title' => $this->user->job_title,
                'organization' => $this->user->organization,
            ],

            'prescription' => [
                'id' => $this->prescription->id,
                'dosage' => $this->prescription->dosage,
                'frequency' => $this->prescription->frequency,
                'status' => $this->prescription->status,
                'start_date' => $this->prescription->start_date,
                'end_date' => $this->prescription->end_date,
                'patient' => [
                    'id' => $this->prescription->patient->id,
                    'name' => $this->prescription->patient->name,
                    'birthdate' => $this->prescription->patient->birthdate,
                ],
                'medication' => [
                    'id' => $this->prescription->medication->id,
                    'name' => $this->prescription->medication->name,
                    'strength' => $this->prescription->medication->strength,
                    'unit' => $this->prescription->medication->unit,
                ],
                'prescriber' => [
                    'id' => $this->prescription->prescriber->id,
                    'name' => $this->prescription->prescriber->name,
                    'job_title' => $this->prescription->prescriber->job_title,
                    'organization' => $this->prescription->prescriber->organization,
                ],
            ],

            'status' => $this->status,
            'administered_at' => $this->administered_at,
            'note' => $this->note,
        ];
    }
}
