<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'birthdate' => $this->birthdate,
            'prescriptions' => $this->prescriptions->map(fn($prescription) => [
                'id' => $prescription->id,
                'medication' => [
                    'id' => $prescription->medication->id,
                    'name' => $prescription->medication->name,
                    'strength' => $prescription->medication->strength,
                    'unit' => $prescription->medication->unit,
                ],
                'prescriber' => [
                    'id' => $prescription->prescriber->id,
                    'name' => $prescription->prescriber->name,
                ],
                'dosage' => $prescription->dosage,
                'frequency' => $prescription->frequency,
                'status' => $prescription->status,
                'start_date' => $prescription->start_date,
                'end_date' => $prescription->end_date,
                'administrations' => $prescription->administrations->map(fn($administration) => [
                    'id' => $administration->id,
                    'status' => $administration->status,
                    'administered_at' => $administration->administered_at,
                    'note' => $administration->note,
                    'user' => [
                        'id' => $administration->user->id,
                        'name' => $administration->user->name,
                    ],
                ]),
            ]),
        ];
    }
}
