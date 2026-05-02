<?php

namespace App\Console\Commands;

use App\Models\Prescription;
use App\Notifications\DoseReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

#[Signature('app:send-dose-reminders')]
#[Description('Send dose reminder notifications to assigned nurses based on prescription frequency')]
class SendDoseReminders extends Command
{
    public function handle(): void
    {
        $now = now();
        $today = $now->toDateString();

        $prescriptions = Prescription::with(['nurse', 'patient', 'medication'])
            ->where('status', 'active')
            ->whereNotNull('nurse_id')
            ->whereDate('start_date', '<=', $today)
            ->where(fn($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', $today))
            ->get();

        $notified = 0;

        foreach ($prescriptions as $prescription) {
            $frequency = $prescription->frequency;
            // Spread doses evenly from 08:00 to 20:00 (12-hour waking window)
            // frequency=1 → [08:00]
            // frequency=2 → [08:00, 20:00]
            // frequency=3 → [08:00, 14:00, 20:00]
            // frequency=4 → [08:00, 12:00, 16:00, 20:00]
            $intervalMinutes = $frequency > 1 ? (12 * 60) / ($frequency - 1) : 0;
            $firstDose = Carbon::parse($today . ' 08:00:00');

            for ($i = 0; $i < $frequency; $i++) {
                $doseTime = $firstDose->copy()->addMinutes($intervalMinutes * $i);
                $doseNumber = $i + 1;

                // Only notify if now is within ±7 minutes of the dose time
                if (abs($now->diffInMinutes($doseTime, false)) > 7) {
                    continue;
                }

                $cacheKey = "dose_reminder:{$prescription->id}:{$today}:{$doseNumber}";

                if (Cache::has($cacheKey)) {
                    continue;
                }

                $prescription->nurse->notify(new DoseReminderNotification($prescription, $doseNumber));
                Cache::put($cacheKey, true, now()->addMinutes(30));
                $notified++;
            }
        }

        Log::info("SendDoseReminders: sent {$notified} dose reminder(s).");
        $this->info("Sent {$notified} dose reminder(s).");
    }
}
