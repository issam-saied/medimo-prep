<?php

namespace App\Services;

use App\Events\PatientCreated;
use App\Events\PatientUpdated;
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
        event(new PatientCreated($patient));
        return $patient;
    }

    public function update(int $id, array $data): Patient
    {
        $patient = Patient::findOrFail($id);
        $fields = ['name', 'birthdate', 'gender', 'room_number'];
        $before = $patient->only($fields);
        $patient->update($data);
        $after = $patient->only($fields);
        $changes = array_filter(
            array_map(fn($f) => $before[$f] != $after[$f] ? ['from' => $before[$f], 'to' => $after[$f]] : null, array_combine($fields, $fields)),
            fn($v) => $v !== null
        );
        Cache::forget('patient_options');
        event(new PatientUpdated($patient, $changes));
        return $patient;
    }
}
