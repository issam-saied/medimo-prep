<?php

namespace App\Listeners;

use App\Events\PatientUpdated;
use App\Models\ActivityLog;

class LogPatientUpdated
{
    public function handle(PatientUpdated $event): void
    {
        ActivityLog::create([
            'action' => 'patient_updated',
            'user_id' => auth()->id(),
            'subject_type' => 'patient',
            'subject_id' => $event->patient->id,
            'description' => 'Patient ' . $event->patient->name . ' was updated',
            'changes' => $event->changes ?: null,
        ]);
    }
}
