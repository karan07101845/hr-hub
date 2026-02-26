<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Employee\TeamsController;

/*
|--------------------------------------------------------------------------
| Team Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->prefix('teams')->group(function () {

    // Get all teams
    Route::get('/', [TeamsController::class, 'index']);

    // Create team
    Route::post('/create', [TeamsController::class, 'store']);

    // Get single team
    Route::get('/{id}', [TeamsController::class, 'show']);

    // Update team
    Route::put('/update/{id}', [TeamsController::class, 'update']);

    // Delete team
    Route::delete('/delete/{id}', [TeamsController::class, 'destroy']);
});