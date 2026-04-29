<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdministrationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MedicationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/patients/export', [PatientController::class, 'export']);
    Route::get('/prescriptions/export', [PrescriptionController::class, 'export']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('patients', PatientController::class);
    Route::apiResource('medications', MedicationController::class);
    Route::apiResource('prescriptions', PrescriptionController::class);
    Route::apiResource('administrations', AdministrationController::class);
    Route::get('/patient-options', [PatientController::class, 'options']);
    Route::get('/medication-options', [MedicationController::class, 'options']);
    Route::get('/prescription-options', [PrescriptionController::class, 'options']);
    Route::get('/activity-logs', [ActivityLogController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
});

Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
