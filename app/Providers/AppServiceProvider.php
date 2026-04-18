<?php

namespace App\Providers;

use App\Events\AdministrationCreated;
use App\Events\AdministrationUpdated;
use App\Events\PrescriptionCreated;
use App\Events\PrescriptionUpdated;
use App\Listeners\LogAdministrationCreated;
use App\Listeners\LogAdministrationUpdated;
use App\Listeners\LogPrescriptionCreated;
use App\Listeners\LogPrescriptionUpdated;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /// The event listener mappings for the application.
    protected $listen = [
        AdministrationCreated::class => [
            LogAdministrationCreated::class,
        ],
        AdministrationUpdated::class => [
            LogAdministrationUpdated::class,
        ],
        PrescriptionCreated::class => [
            LogPrescriptionCreated::class,
        ],
        PrescriptionUpdated::class => [
            LogPrescriptionUpdated::class,
        ],
    ];
}
