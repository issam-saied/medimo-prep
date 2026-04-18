<?php

namespace App\Listeners;

use App\Events\AdministrationCreated;
use App\Models\ActivityLog;

class LogAdministrationCreated
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
    public function handle(AdministrationCreated $event): void
    {
        $administration = $event->administration;

        ActivityLog::create([
            'action' => 'administration_created',
            'user_id' => $administration->user_id,
            'subject_type' => 'administration',
            'subject_id' => $administration->id,
            'description' => 'Administration created with status ' . $administration->status,
        ]);
    }
}
