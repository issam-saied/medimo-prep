<?php

use App\Http\Controllers\AdministrationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MedicationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('users', UserController::class);
    Route::apiResource('patients', PatientController::class);
    Route::apiResource('medications', MedicationController::class);
    Route::apiResource('prescriptions', PrescriptionController::class);
    Route::apiResource('administrations', AdministrationController::class);
    Route::get('/patient-options', [PatientController::class, 'options']);
    Route::get('/prescription-options', [PrescriptionController::class, 'options']);
});

Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
