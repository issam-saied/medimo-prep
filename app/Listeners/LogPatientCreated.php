<?php

namespace App\Listeners;

use App\Events\PatientCreated;
use App\Models\ActivityLog;

class LogPatientCreated
{
    public function handle(PatientCreated $event): void
    {
        ActivityLog::create([
            'action' => 'patient_created',
            'user_id' => auth()->id(),
            'subject_type' => 'patient',
            'subject_id' => $event->patient->id,
            'description' => 'Patient ' . $event->patient->name . ' was created',
        ]);
    }
}
