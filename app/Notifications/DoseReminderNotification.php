<?php

namespace App\Notifications;

use App\Models\Prescription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DoseReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Prescription $prescription,
        public int $doseNumber,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message'         => 'Time to administer ' . $this->prescription->medication->name . ' ' . $this->prescription->dosage . ' for ' . $this->prescription->patient->name . ' (dose ' . $this->doseNumber . '/' . $this->prescription->frequency . ')',
            'prescription_id' => $this->prescription->id,
            'patient_name'    => $this->prescription->patient->name,
            'medication_name' => $this->prescription->medication->name,
            'dose_number'     => $this->doseNumber,
            'frequency'       => $this->prescription->frequency,
        ];
    }
}
