<?php

namespace App\Http\Controllers;

use App\Events\PatientDeleted;
use App\Http\Requests\ListPatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Http\Resources\PatientDetailResource;
use App\Http\Resources\PatientResource;
use App\Services\PatientService;
use App\Http\Requests\StorePatientRequest;

use App\Models\Patient;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
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
        event(new PatientDeleted($patient));
        return response()->noContent();
    }

    public function export(Request $request)
    {
        $format = $request->query('format', 'csv');

        $query = Patient::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('birthdate')) {
            $query->whereDate('birthdate', $request->birthdate);
        }

        $patients = $query->orderBy('name')->get();

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('exports.patients', compact('patients'))->setPaper('a4');
            return $pdf->download('patients.pdf');
        }

        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="patients.csv"'];

        $callback = function () use ($patients) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Name', 'Birthdate']);
            foreach ($patients as $patient) {
                fputcsv($handle, [
                    $patient->id,
                    $patient->name,
                    $patient->birthdate ? \Carbon\Carbon::parse($patient->birthdate)->format('Y-m-d') : '',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function options()
    {
        $patients = Cache::remember('patient_options', 3600, function () {
            return Patient::select('id', 'name')->orderBy('name')->get()->toArray();
        });

        return response()->json(['data' => $patients]);
    }
}
