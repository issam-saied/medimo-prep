<?php

namespace App\Listeners;

use App\Events\PatientDeleted;
use App\Models\ActivityLog;

class LogPatientDeleted
{
    public function handle(PatientDeleted $event): void
    {
        ActivityLog::create([
            'action' => 'patient_deleted',
            'user_id' => auth()->id(),
            'subject_type' => 'patient',
            'subject_id' => $event->patient->id,
            'description' => 'Patient ' . $event->patient->name . ' was deleted',
        ]);
    }
}
