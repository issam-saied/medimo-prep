<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListPrescriptionRequest;
use App\Http\Requests\StorePrescriptionRequest;
use App\Http\Requests\UpdatePrescriptionRequest;
use App\Http\Resources\PrescriptionResource;
use App\Models\Prescription;
use App\Services\PrescriptionService;
use Barryvdh\DomPDF\Facade\Pdf;
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
        $this->authorize('create', Prescription::class); // no record yet, pass the class

        $data = $request->validated();

        $prescription = $this->prescriptionService->create($data);

        return (new PrescriptionResource($prescription))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdatePrescriptionRequest $request, $id)
    {
        $prescription = Prescription::findOrFail($id); // find FIRST
        $this->authorize('update', $prescription);     // then authorize against the record

        $data = $request->validated();

        $prescription = $this->prescriptionService->update($id, $data);

        return new PrescriptionResource($prescription);
    }

    public function destroy($id)
    {
        $prescription = Prescription::findOrFail($id);
        $this->authorize('delete', $prescription);
        $prescription->delete();
        return response()->noContent();
    }

    public function export(Request $request)
    {
        $format = $request->query('format', 'csv');

        $query = Prescription::with(['patient', 'medication', 'prescriber']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('patient_name')) {
            $query->whereHas('patient', fn($q) => $q->where('name', 'like', '%' . $request->patient_name . '%'));
        }

        if ($request->filled('prescriber_name')) {
            $query->whereHas('prescriber', fn($q) => $q->where('name', 'like', '%' . $request->prescriber_name . '%'));
        }

        if ($request->filled('medication_name')) {
            $query->whereHas('medication', fn($q) => $q->where('name', 'like', '%' . $request->medication_name . '%'));
        }

        $prescriptions = $query->orderByDesc('start_date')->get();

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('exports.prescriptions', compact('prescriptions'))->setPaper('a4', 'landscape');
            return $pdf->download('prescriptions.pdf');
        }

        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="prescriptions.csv"'];

        $callback = function () use ($prescriptions) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Patient', 'Medication', 'Prescriber', 'Dosage', 'Frequency', 'Status', 'Start Date', 'End Date']);
            foreach ($prescriptions as $p) {
                fputcsv($handle, [
                    $p->id,
                    $p->patient->name,
                    $p->medication->name . ' ' . $p->medication->strength . $p->medication->unit,
                    $p->prescriber->name,
                    $p->dosage,
                    $p->frequency . 'x/day',
                    $p->status,
                    \Carbon\Carbon::parse($p->start_date)->format('Y-m-d'),
                    $p->end_date ? \Carbon\Carbon::parse($p->end_date)->format('Y-m-d') : '',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function options(Request $request)
    {
        $patientId = $request->query('patient_id');
        $user = auth()->user();

        $prescriptions = Prescription::query()
            ->with(['medication:id,name'])
            ->where('status', 'active')
            ->where('patient_id', $patientId)
            ->when($user->role === 'nurse', fn($q) => $q->where('nurse_id', $user->id))
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
