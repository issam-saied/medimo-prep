<?php

namespace App\Services;

use App\Domain\Administration\AdministrationValidator;
use App\Events\AdministrationUpdated;
use App\Models\Administration;
use App\Models\Prescription;
use App\Models\User;
use App\Notifications\MissedOrRefusedDoseNotification;
use App\Notifications\NewPrescriptionNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use App\Events\AdministrationCreated;
use Illuminate\Pagination\LengthAwarePaginator;

class AdministrationService
{
    public function getFilteredAdministrations(array $filters): LengthAwarePaginator
    {
        $query = Administration::with([
            'user:id,name,job_title,role,organization',
            'prescription:id,patient_id,medication_id,prescriber_id,dosage,frequency,status',
            'prescription.patient:id,name',
            'prescription.medication:id,name',
        ]);

        // Filter by status if provided in the request
        if (!empty($filters['status'] ?? null)) {
            $query->where('status', $filters['status']);
        }

        $relationNameFilters = [
            'patient_name' => 'patient',
            'medication_name' => 'medication',
            'prescriber_name' => 'prescriber',
        ];

        // Filter by patient_id if provided in the request
/*        if (!empty($filters['patient_name'])) {

            $query->whereHas('prescription.patient', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['patient_name'] . '%');
            });
        }*/

        foreach ($relationNameFilters as $filterKey => $relation) {
            $searchValue = $filters[$filterKey] ?? null;

            if (!empty($searchValue)) {
                $query->whereHas('prescription.' . $relation, function ($q) use ($searchValue) {
                    $q->where('name', 'like', '%' . $searchValue . '%');
                });
            }
        }

        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $sortField = $filters['sort_field'] ?? 'administered_at';

        $sortableRelations = [
            'patient' => [
                'table' => 'patients',
                'foreign_key' => 'prescriptions.patient_id',
                'column' => 'patients.name',
            ],
            'medication' => [
                'table' => 'medications',
                'foreign_key' => 'prescriptions.medication_id',
                'column' => 'medications.name',
            ],
            'prescriber' => [
                'table' => 'users',
                'foreign_key' => 'prescriptions.prescriber_id',
                'column' => 'users.name',
            ],
        ];

        if (isset($sortableRelations[$sortField])) {
            $relation = $sortableRelations[$sortField];

            $query->join('prescriptions', 'administrations.prescription_id', '=', 'prescriptions.id')
                ->join($relation['table'], $relation['foreign_key'], '=', $relation['table'] . '.id')
                ->orderBy($relation['column'], $sortDirection)
                ->select('administrations.*');
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        return $query->paginate(5);
    }

    public function create(array $data): Administration
    {
        //transaction to ensure data integrity, if any validation fails,
        //the transaction will be rolled back and no administration record will be created
        return DB::transaction(function () use ($data) {

            $prescription = Prescription::findOrFail($data['prescription_id']);

            //Business rule: Administration allowed only for active prescription
            if ($prescription->status !== 'active') {
                throw ValidationException::withMessages([
                    'prescription_id' => 'Cannot administer medication on non-active prescription.',
                ]);
            }

            //Business rule: Nurse can only administer prescriptions assigned to them
            if (auth()->user()->hasRole('nurse') && $prescription->nurse_id !== auth()->id()) {
                throw ValidationException::withMessages([
                    'prescription_id' => 'You are not the assigned nurse for this prescription.',
                ]);
            }

            $validator = new AdministrationValidator();

            if ($validator->isBeforeStart($data['administered_at'], $prescription->start_date)) {
                throw ValidationException::withMessages([
                    'administered_at' => 'Administration time cannot be before the prescription start date.',
                ]);
            }

            if ($prescription->end_date !== null && $validator->isAfterEnd($data['administered_at'], $prescription->end_date)) {
                throw ValidationException::withMessages([
                    'administered_at' => 'Administration time cannot be after the prescription end date.',
                ]);
            }

            $data['user_id'] = auth()->id();

            $administration = Administration::with(['prescription.patient', 'prescription.medication'])->find(
                Administration::create($data)->id
            );

            event(new AdministrationCreated($administration));

            //Dose missed/refused → notify prescribing doctor + all admins
            if (in_array($data['status'], ['refused','missed']) ) {

                $recipients = User::where('id', $administration->prescription->prescriber_id)
                    ->orWhere('role', 'admin')
                    ->get();

                Notification::send($recipients, new MissedOrRefusedDoseNotification($administration));
            }

            return $administration;
        });
    }

    public function update(int $id, array $data): Administration
    {
        $administration = Administration::findOrFail($id);

        $oldNote = $administration->note;

        $administration->update([
            'note' => $data['note'] ?? $administration->note,
        ]);

        $changes = [];
        if ($oldNote != $administration->note) {
            $changes['note'] = ['from' => $oldNote, 'to' => $administration->note];
        }

        event(new AdministrationUpdated($administration, $changes));

        return $administration;
    }
}
