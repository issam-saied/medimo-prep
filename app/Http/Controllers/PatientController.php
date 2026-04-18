<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListPatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Http\Resources\PatientResource;
use App\Services\PatientService;
use App\Http\Requests\StorePatientRequest;

use App\Models\Patient;

class PatientController extends Controller
{
    protected PatientService $patientService;

    public function __construct(PatientService $patientService)
    {
        $this->patientService = $patientService;
    }

    public function index(ListPatientRequest $request)
    {
        $data = $request->validated();

        $patients = $this->patientService->getFilteredPatients($data);
        return PatientResource::collection($patients);
    }

    public function show($id)
    {
        //no collection method is needed here since we are only returning a single patient,
        //so we can directly return the PatientResource for the found patient
        return new PatientResource(Patient::findOrFail($id));
    }

    public function store(StorePatientRequest $request)
    {
        $data = $request->validated();

        $patient = $this->patientService->create($data);

        return (new PatientResource($patient))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdatePatientRequest $request, $id)
    {
        $data = $request->validated();

        $patient = $this->patientService->update($id, $data);

        return new PatientResource($patient);
    }

    public function options()
    {
        $patients = Patient::query()
/*            ->whereHas('prescriptions', function ($query) {
                $query->where('status', 'active');
            })*/
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $patients,
        ]);
    }
}
