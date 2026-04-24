<?php

namespace App\Services;

use App\Events\PrescriptionCreated;
use App\Events\PrescriptionUpdated;
use App\Models\Prescription;
use App\Models\User;
use App\Notifications\NewPrescriptionNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Illuminate\Pagination\LengthAwarePaginator;

class PrescriptionService
{
    public function getFilteredPrescriptions(array $filters): LengthAwarePaginator
    {
        $query = Prescription::with([
            'patient:id,name,birthdate',
            'medication:id,name,strength,unit',
            'prescriber:id,name,job_title,role,organization',
            'createdByUser:id,name,job_title,role,organization',
        ]);

        if (!empty($filters['status'] ?? null)) {
            $query->where('status', $filters['status']);
        }

        $relationNameFilters = [
            'patient_name' => 'patient',
            'medication_name' => 'medication',
            'prescriber_name' => 'prescriber',
        ];

        if (!empty($filters['patient_name'])) {
            $query->whereHas('patient', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['patient_name'] . '%');
            });
        }

        foreach ($relationNameFilters as $filterKey => $relation) {
            if (!empty($filters[$filterKey] ?? null)) {
                $searchValue = $filters[$filterKey];
                $query->whereHas($relation, function ($q) use ($searchValue) {
                    $q->where('name', 'like', '%' . $searchValue . '%');
                });
            }
        }

        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $sortField = $filters['sort_field'] ?? 'start_date';

        $sortableRelations = [
            'patient' => [
                'table' => 'patients',
                'foreign_key' => 'patient_id',
                'column' => 'patients.name',
            ],
            'medication' => [
                'table' => 'medications',
                'foreign_key' => 'medication_id',
                'column' => 'medications.name',
            ],
            'prescriber' => [
                'table' => 'users',
                'foreign_key' => 'prescriber_id',
                'column' => 'users.name',
            ],
        ];

        if (isset($sortableRelations[$sortField])) {
            $relation = $sortableRelations[$sortField];

            $query->join($relation['table'], $relation['foreign_key'], '=', $relation['table'] . '.id')
                ->orderBy($relation['column'], $sortDirection)
                ->select('prescriptions.*');
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        //return $query->get();
        return $query->paginate(5);
    }

    public function create(array $data): Prescription
    {
        // Business rule: geen 2 active tegelijk
        if ($data['status'] === 'active') {

            $exists = Prescription::where('patient_id', $data['patient_id'])
                ->where('medication_id', $data['medication_id'])
                ->where('status', 'active')
                ->exists();

            if ($exists) {

                throw ValidationException::withMessages([
                    'medication_id' => 'This patient already has an active prescription for this medication.',
                ]);
            }
        }

        if (in_array($data['status'], ['completed', 'stopped']) && empty($data['end_date'])) {
            throw ValidationException::withMessages([
                'end_date' => 'End date is required when status is completed or stopped.',
            ]);
        }
        // Set the created_by_user_id to the currently authenticated user's ID
        $data['created_by_user_id'] = auth()->id();

        $prescription = Prescription::with(['patient', 'medication'])->find(
            Prescription::create($data)->id
        );

        event(new PrescriptionCreated($prescription));

        $nurses = User::where('role', 'nurse')->get();
        Notification::send($nurses, new NewPrescriptionNotification($prescription));

        return $prescription;
    }

    public function update(int $id, array $data): Prescription
    {
        $prescription = Prescription::findOrFail($id);

        // Business rule: geen 2 active tegelijk
        if (array_key_exists('status', $data) && $data['status'] === 'active') {

            $exists = Prescription::where('patient_id', $prescription['patient_id'])
                ->where('medication_id', $prescription['medication_id'])
                ->where('status', 'active')
                ->where('id', '!=', $prescription->id) //ignore current prescription
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'medication_id' => 'This patient already has an active prescription for this medication.',
                ]);
            }
        }

        $status = $data['status'] ?? $prescription->status;
        $endDate = $data['end_date'] ?? $prescription->end_date;

        if (in_array($status, ['completed', 'stopped']) && empty($endDate)) {
            throw ValidationException::withMessages([
                'end_date' => 'End date is required when status is completed or stopped.',
            ]);
        }

        $prescription->update($data);

        event(new PrescriptionUpdated($prescription));

        return $prescription;

    }
}
