<?php

namespace App\Listeners;

use App\Events\PrescriptionUpdated;
use App\Models\ActivityLog;

class LogPrescriptionUpdated
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PrescriptionUpdated $event): void
    {
        $prescription = $event->prescription;

        ActivityLog::create([
            'action' => 'prescription_updated',
            'user_id' => $prescription->created_by_user_id,
            'subject_type' => 'prescription',
            'subject_id' => $prescription->id,
            'description' => 'Prescription updated with status ' . $prescription->status,
        ]);
    }
}
