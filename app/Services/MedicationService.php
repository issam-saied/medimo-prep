<?php

namespace App\Services;

use App\Models\Medication;
use Illuminate\Pagination\LengthAwarePaginator;

class MedicationService
{
    public function getFilteredMedications(array $filters): LengthAwarePaginator
    {
        $query = Medication::query();

        if (!empty($filters['name'] ?? null)) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }
        if (!empty($filters['form'] ?? null)) {
            $query->where('form', 'like', '%' . $filters['form'] . '%');
        }
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $sortField = $filters['sort_field'] ?? 'id';

        $query->orderBy($sortField, $sortDirection);

        //return $query->get();
        return $query->paginate(5);
    }

    public function create(array $data): Medication
    {
        return Medication::create($data);
    }

    public function update(int $id, array $data): Medication
    {
        $patient = Medication::findOrFail($id);

        $patient->update($data);

        return $patient;
    }
}
