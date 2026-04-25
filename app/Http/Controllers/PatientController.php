<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListPatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Http\Resources\PatientDetailResource;
use App\Http\Resources\PatientResource;
use App\Services\PatientService;
use App\Http\Requests\StorePatientRequest;

use App\Models\Patient;
use Illuminate\Support\Facades\Cache;

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
        $patient = Patient::with([
            'prescriptions.medication',
            'prescriptions.prescriber',
            'prescriptions.administrations.user',
        ])->findOrFail($id);

        return new PatientDetailResource($patient);
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

    public function destroy($id)
    {
        $patient = Patient::findOrFail($id);
        $this->authorize('delete', $patient);
        $patient->delete();
        Cache::forget('patient_options');
        Cache::forget('dashboard_global_stats');
        return response()->noContent();
    }

    public function options()
    {
        $patients = Cache::remember('patient_options', 3600, function () {
            return Patient::select('id', 'name')->orderBy('name')->get()->toArray();
        });

        return response()->json(['data' => $patients]);
    }
}
