<?php

namespace App\Services;

use App\Models\Medication;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

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
        $medication = Medication::create($data);
        Cache::forget('medication_options');
        return $medication;
    }

    public function update(int $id, array $data): Medication
    {
        $medication = Medication::findOrFail($id);
        $medication->update($data);
        Cache::forget('medication_options');
        return $medication;
    }
}
