<?php

namespace App\Providers;

use App\Events\AdministrationCreated;
use App\Events\AdministrationUpdated;
use App\Events\PatientCreated;
use App\Events\PatientDeleted;
use App\Events\PatientUpdated;
use App\Events\PrescriptionCreated;
use App\Events\PrescriptionUpdated;
use App\Listeners\LogAdministrationCreated;
use App\Listeners\LogAdministrationUpdated;
use App\Listeners\LogPatientCreated;
use App\Listeners\LogPatientDeleted;
use App\Listeners\LogPatientUpdated;
use App\Listeners\LogPrescriptionCreated;
use App\Listeners\LogPrescriptionUpdated;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $listen = [
        AdministrationCreated::class => [LogAdministrationCreated::class],
        AdministrationUpdated::class => [LogAdministrationUpdated::class],
        PrescriptionCreated::class   => [LogPrescriptionCreated::class],
        PrescriptionUpdated::class   => [LogPrescriptionUpdated::class],
        PatientCreated::class        => [LogPatientCreated::class],
        PatientUpdated::class        => [LogPatientUpdated::class],
        PatientDeleted::class        => [LogPatientDeleted::class],
    ];
}
