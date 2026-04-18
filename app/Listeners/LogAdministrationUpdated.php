<?php

namespace App\Listeners;

use App\Events\AdministrationUpdated;
use App\Models\ActivityLog;

class LogAdministrationUpdated
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
    public function handle(AdministrationUpdated $event): void
    {
        $administration = $event->administration;

        ActivityLog::create([
            'action' => 'administration_created',
            'user_id' => $administration->user_id,
            'subject_type' => 'administration',
            'subject_id' => $administration->id,
            'description' => 'Note of administration has been modified',
        ]);
    }
}
