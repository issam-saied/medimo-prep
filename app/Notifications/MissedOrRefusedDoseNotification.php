<?php

namespace App\Notifications;

use App\Models\Administration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MissedOrRefusedDoseNotification extends Notification
{
    use Queueable;

    public function __construct(public Administration $administration) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message'         => 'Dose ' . $this->administration->status . ' for ' . $this->administration->prescription->patient->name . ' - ' . $this->administration->prescription->medication->name,
            'administration_id' => $this->administration->id,
            'administration_status' => $this->administration->status,
            'patient_name'    => $this->administration->prescription->patient->name,
            'medication_name' => $this->administration->prescription->medication->name,
        ];
    }
}
