<?php

namespace App\Listeners;

use App\Events\PrescriptionCreated;
use App\Models\ActivityLog;

class LogPrescriptionCreated
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
    public function handle(PrescriptionCreated $event): void
    {
        $prescription = $event->prescription;

        ActivityLog::create([
            'action' => 'prescription_created',
            'user_id' => $prescription->created_by_user_id,
            'subject_type' => 'prescription',
            'subject_id' => $prescription->id,
            'description' => 'prescription created with status ' . $prescription->status,
        ]);
    }
}
