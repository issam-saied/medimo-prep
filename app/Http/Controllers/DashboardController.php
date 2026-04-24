<?php

namespace App\Http\Controllers;

use App\Models\Administration;
use App\Models\Patient;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

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

        $todayAdministrations = Administration::whereDate('administered_at', $date);

        $recentActivity = Administration::with([
            'user:id,name',
            'prescription.patient:id,name',
            'prescription.medication:id,name,strength,unit',
        ])
            ->latest('administered_at')
            ->limit(5)
            ->get()
            ->map(fn($adm) => [
                'id'             => $adm->id,
                'status'         => $adm->status,
                'administered_at' => $adm->administered_at,
                'nurse'          => $adm->user->name,
                'patient'        => $adm->prescription->patient->name,
                'medication'     => $adm->prescription->medication->name . ' ' . $adm->prescription->medication->strength,
            ]);

        [$activePrescriptions, $totalPatients] = Cache::remember('dashboard_global_stats', 300, function () {
            return [
                Prescription::where('status', 'active')->count(),
                Patient::count(),
            ];
        });

        $stats = [
            'active_prescriptions' => $activePrescriptions,
            'total_patients'       => $totalPatients,
            'given_today'          => (clone $todayAdministrations)->where('status', 'given')->count(),
            'missed_today'         => (clone $todayAdministrations)->where('status', 'missed')->count(),
            'refused_today'        => (clone $todayAdministrations)->where('status', 'refused')->count(),
            'remaining_today'      => $prescriptions->sum('remaining'),
        ];

        return response()->json(['data' => $prescriptions, 'stats' => $stats, 'recent_activity' => $recentActivity]);
    }
}
