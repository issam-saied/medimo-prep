<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListMedicationRequest;
use App\Http\Requests\StoreMedicationRequest;
use App\Http\Requests\UpdateMedicationRequest;
use App\Http\Resources\MedicationResource;
use App\Models\Medication;
use App\Models\Patient;
use App\Services\MedicationService;
use Illuminate\Support\Facades\Cache;

class MedicationController extends Controller
{
    protected MedicationService $medicationService;

    public function __construct(MedicationService $medicationService)
    {
        $this->medicationService = $medicationService;
    }

    public function index(ListMedicationRequest $request)
    {
        $data = $request->validated();

        $patients = $this->medicationService->getFilteredMedications($data);
        return MedicationResource::collection($patients);
    }

    public function show($id)
    {
        //no collection method is needed here since we are only returning a single patient,
        //so we can directly return the PatientResource for the found patient
        return new MedicationResource(Medication::findOrFail($id));
    }

    public function store(StoreMedicationRequest $request)
    {
        $data = $request->validated();

        $patient = $this->medicationService->create($data);

        return (new MedicationResource($patient))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateMedicationRequest $request, $id)
    {
        $data = $request->validated();

        $patient = $this->medicationService->update($id, $data);

        return new MedicationResource($patient);
    }

    public function destroy($id)
    {
        $medication = Medication::findOrFail($id);
        $this->authorize('delete', $medication);
        $medication->delete();
        Cache::forget('medication_options');
        return response()->noContent();
    }

    public function options()
    {
        $medications = Cache::remember('medication_options', 3600, function () {
            return Medication::orderBy('name')->get()->toArray();
        });

        return response()->json(['data' => $medications]);
    }
}
