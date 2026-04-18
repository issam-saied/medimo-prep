<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListPrescriptionRequest;
use App\Http\Requests\StorePrescriptionRequest;
use App\Http\Requests\UpdatePrescriptionRequest;
use App\Http\Resources\PrescriptionResource;
use App\Models\Prescription;
use App\Services\PrescriptionService;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    protected PrescriptionService $prescriptionService;

    public function __construct(PrescriptionService $prescriptionService)
    {
        $this->prescriptionService = $prescriptionService;
    }

    public function index(ListPrescriptionRequest $request)
    {
        $data = $request->validated();
        $prescriptions = $this->prescriptionService->getFilteredPrescriptions($data);
        return PrescriptionResource::collection($prescriptions);
    }

    public function show($id)
    {
        // Load the prescription along with its related patient and medication data to optimize the query
        $prescription = Prescription::with('patient', 'medication', 'prescriber', 'createdByUser')->findOrFail($id);

        return new PrescriptionResource($prescription);
    }

    public function store(StorePrescriptionRequest $request)
    {
        $data = $request->validated();

        $prescription = $this->prescriptionService->create($data);

        return (new PrescriptionResource($prescription))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdatePrescriptionRequest $request, $id)
    {
        $data = $request->validated();

        $prescription = $this->prescriptionService->update($id, $data);

        return new PrescriptionResource($prescription);
    }

    public function options(Request $request)
    {
        $patientId = $request->query('patient_id');

        $prescriptions = Prescription::query()
            ->with(['medication:id,name'])
            ->where('status', 'active')
            ->where('patient_id', $patientId)
            ->orderByDesc('id')
            ->get([
                'id',
                'patient_id',
                'medication_id',
                'dosage',
                'frequency',
                'status',
            ]);

        return response()->json([
            'data' => $prescriptions,
        ]);
    }
}
