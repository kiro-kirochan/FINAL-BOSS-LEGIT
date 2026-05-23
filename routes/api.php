<?php

use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AppointmentController;
use Illuminate\Support\Facades\Route;

// Dashboard Stats
Route::get('/stats', [AppointmentController::class, 'stats']);

// Specializations for dropdowns (must be BEFORE apiResource to avoid /doctors/{id} conflict)
Route::get('/doctors/specializations', [DoctorController::class, 'specializations']);

// Doctors CRUD — registers: GET/POST /api/doctors, GET/PUT/DELETE /api/doctors/{id}
Route::apiResource('doctors', DoctorController::class);

// Patients CRUD
Route::apiResource('patients', PatientController::class);

// Appointments CRUD
Route::apiResource('appointments', AppointmentController::class);
