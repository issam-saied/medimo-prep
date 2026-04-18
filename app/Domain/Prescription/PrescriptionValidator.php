<?php

namespace App\Domain\Prescription;

class PrescriptionValidator
{
    //deze class is verantwoordelijk voor het valideren van de status en einddatum van een recept.

    public function requiresEndDate(string $status, ?string $endDate): bool
    {
        return in_array($status, ['completed', 'stopped']) && empty($endDate);
    }
}
