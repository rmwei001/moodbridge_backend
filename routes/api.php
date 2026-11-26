<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientAuthController;
use App\Http\Controllers\MoodEntryController;

// Clinician authentication & verification
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware('signed')
    ->name('verification.verify');
Route::post('/resend-clinician-verification', [VerificationController::class, 'resendClinicianVerification']);

// Patient authentication & verification
Route::post('/patient/verify-otp', [PatientAuthController::class, 'verifyOtp']);
Route::post('/patient/resend-otp', [PatientAuthController::class, 'resendOtp']);
Route::post('/patient/login', [PatientAuthController::class, 'login']);

// Authenticated routes (Clinicians and Patients)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Patient management routes for clinicians
    Route::post('/patients', [PatientController::class, 'addPatient']);
    Route::get('/patients', [PatientController::class, 'viewPatients']);
    Route::get('/patients/{id}', [PatientController::class, 'viewPatient']);
    Route::match(['put', 'post'], '/patients/{id}', [PatientController::class, 'updatePatient']);
    Route::delete('/patients/{id}', [PatientController::class, 'deactivatePatient']);

    // Patient mood entries
    Route::post('/mood-entries', [MoodEntryController::class, 'store']);
    Route::get('/patient/mood-entries', [MoodEntryController::class, 'patientEntries']);
    Route::get('/patients/{id}/mood-entries', [MoodEntryController::class, 'clinicianPatientEntries']);
});
