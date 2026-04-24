<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', today()->toDateString());
        $user = Auth::user();

        $query = Prescription::with([
            'patient',
            'medication',
            'administrations' => fn($q) => $q->whereDate('administered_at', $date),
        ])
            ->where('status', 'active')
            ->whereDate('start_date', '<=', $date)
            ->where(fn($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', $date));

        if ($user->hasRole('doctor')) {
            $query->where('prescriber_id', $user->id);
        }

        if ($user->hasRole('nurse')) {
            $query->orderByRaw("
                  EXISTS (
                      SELECT 1 FROM administrations
                      WHERE administrations.prescription_id = prescriptions.id
                      AND administrations.user_id = ?
                      AND DATE(administrations.administered_at) = ?
                  ) DESC
              ", [$user->id, $date]);
        }

        if ($request->filled('patient_name')) {
            $query->whereHas('patient', fn($q) =>
            $q->where('name', 'like', '%' . $request->input('patient_name') . '%')
            );
        }

        $prescriptions = $query->get()->map(fn($prescription) => [
            'id'                 => $prescription->id,
            'patient_name'       => $prescription->patient->name,
            'medication'         => $prescription->medication->name . ' ' . $prescription->medication->strength,
            'frequency'          => $prescription->frequency,
            'administered_count' => $prescription->administrations->count(),
            'remaining'          => max(0, $prescription->frequency - $prescription->administrations->count()),
            'administered_by_me' => $user->hasRole('nurse') && $prescription->administrations
                ->contains('user_id', $user->id),
        ]);

        return response()->json(['data' => $prescriptions]);
    }
}
