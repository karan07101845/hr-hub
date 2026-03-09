<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Leaves\LeavesController;

Route::middleware( ['auth:sanctum'])->prefix('leaves')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Employee Routes
    |--------------------------------------------------------------------------
    */

    // Apply Leave
    Route::post('/apply', [LeavesController::class, 'apply']);

    // Get My Leaves
    Route::get('/my-leaves', [LeavesController::class, 'myLeaves']);

    /*
    |--------------------------------------------------------------------------
    | Manager / Admin Routes
    |--------------------------------------------------------------------------
    */

    // Get All Leaves
    Route::get('/all-leaves', [LeavesController::class, 'allLeaves']);

    // Approve / Reject Leave
    Route::post('/update-status/{id}', [LeavesController::class, 'updateStatus']);

    /*
    |--------------------------------------------------------------------------
    | Optional
    |--------------------------------------------------------------------------
    */

    // Delete Leave
    Route::delete('/delete/{id}', [LeavesController::class, 'destroy']);
});