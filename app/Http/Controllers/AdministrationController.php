<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListAdministrationRequest;
use App\Http\Requests\StoreAdministrationRequest;
use App\Http\Requests\UpdateAdministrationRequest;
use App\Http\Resources\AdministrationResource;
use App\Models\Administration;
use App\Services\AdministrationService;

class AdministrationController extends Controller
{
    protected AdministrationService $administrationService;

    public function __construct(AdministrationService $administrationService)
    {
        $this->administrationService = $administrationService;
    }

    public function index(ListAdministrationRequest $request)
    {
        $data = $request->validated();
        $administrations = $this->administrationService->getFilteredAdministrations($data);
        return AdministrationResource::collection($administrations);
    }

/*    public function index(Request $request)
    {
        $query = Administration::with([
            'user:id,name,job_title,role,organization',
            'prescription:id,patient_id,medication_id,dosage,frequency,prescriber_id,status',
        ]);

        // Filter by patient_id if provided in the request
        if ($request->filled('patient_id')) {
            $patientId = $request->patient_id;

            $query->whereHas('prescription', function ($q) use ($patientId) {
                $q->where('patient_id', $patientId);
            });
        }

        // Filter by status if provided in the request
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $administrations = $query->get();

        //use collection to transform the collection of administrations using the AdministrationResource
        return AdministrationResource::collection($administrations);
    }*/

    public function show($id)
    {
        // Load the administration along with its related patient and medication data
        $administration = Administration::with('user', 'prescription')->findOrFail($id);

        return new AdministrationResource($administration);
    }

    public function store(StoreAdministrationRequest $request)
    {
        $this->authorize('create', Administration::class);

        $data = $request->validated();

        $administration = $this->administrationService->create($data);

        return (new AdministrationResource($administration))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy($id)
    {
        $administration = Administration::findOrFail($id);
        $this->authorize('delete', $administration);
        $administration->delete();
        return response()->noContent();
    }

    public function update(UpdateAdministrationRequest $request, $id)
    {
        $administration = Administration::findOrFail($id); // find FIRST
        $this->authorize('update', $administration);       // then authorize against the record

        $data = $request->validated();

        $administration = $this->administrationService->update($id, $data);

        return new AdministrationResource($administration);
    }
}
