<?php

namespace App\Console\Commands;

use App\Events\PrescriptionUpdated;
use App\Models\Prescription;
use App\Models\User;
use App\Notifications\PrescriptionsExpiredNotification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

#[Signature('app:expire-prescriptions')]
#[Description('Mark active prescriptions as completed when end_date has passed')]
class ExpirePrescriptions extends Command
{
    public function handle()
    {
        $expired = Prescription::query()
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '<', now())
            ->get();

        foreach ($expired as $prescription) {
            $prescription->update(['status' => 'completed']);
            event(new PrescriptionUpdated($prescription));
        }

        $this->info("Marked {$expired->count()} prescription(s) as completed.");

        if($expired->count() > 0){
            $recipients = User::Where('role', 'admin')->get();
            Notification::send($recipients, new PrescriptionsExpiredNotification($expired->count()));
        }
    }
}
