<?php

namespace App\Services;

use App\Models\Patient;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class PatientService
{
    public function getFilteredPatients(array $filters): LengthAwarePaginator
    {
        $query = Patient::query();

        if (!empty($filters['name'] ?? null)) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['birthdate'] ?? null)) {
            $query->whereDate('birthdate', $filters['birthdate']);
        }

        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $sortField = $filters['sort_field'] ?? 'id';

        $query->orderBy($sortField, $sortDirection);

        //return $query->get();
        return $query->paginate(5);
    }

    public function create(array $data): Patient
    {
        $patient = Patient::create($data);
        Cache::forget('patient_options');
        Cache::forget('dashboard_global_stats');
        return $patient;
    }

    public function update(int $id, array $data): Patient
    {
        $patient = Patient::findOrFail($id);
        $patient->update($data);
        Cache::forget('patient_options');
        return $patient;
    }
}
