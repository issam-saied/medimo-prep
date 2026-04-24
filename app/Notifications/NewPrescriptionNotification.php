<?php

namespace App\Notifications;

use App\Models\Prescription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewPrescriptionNotification extends Notification
{
    use Queueable;

    public function __construct(public Prescription $prescription) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message'         => 'New prescription created for ' . $this->prescription->patient->name,
            'prescription_id' => $this->prescription->id,
            'patient_name'    => $this->prescription->patient->name,
            'medication_name' => $this->prescription->medication->name,
        ];
    }
}
