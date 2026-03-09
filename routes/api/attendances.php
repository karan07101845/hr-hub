<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Attendance\AttendanceController;

Route::middleware(['auth:sanctum'])->prefix('attendances')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Role Based Attendance View
    |--------------------------------------------------------------------------
    */

    // Admin / Manager / Team Leader / Employee
    Route::get('/', [AttendanceController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | Admin Only
    |--------------------------------------------------------------------------
    */

    Route::post('/upload', [AttendanceController::class, 'upload'])
        ->middleware('role:admin');

    Route::put('/update/{id}', [AttendanceController::class, 'update'])
        ->middleware('role:admin');

    Route::delete('/delete/{id}', [AttendanceController::class, 'destroy'])
        ->middleware('role:admin');

    /*
    |--------------------------------------------------------------------------
    | Logged-in User
    |--------------------------------------------------------------------------
    */

    Route::get('/my-report', [AttendanceController::class, 'myReport']);

    Route::get('/last-status', [AttendanceController::class, 'lastStatus']);
});