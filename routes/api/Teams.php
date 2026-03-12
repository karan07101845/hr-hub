<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Employee\TeamsController;

Route::middleware(['auth:sanctum'])->prefix('teams')->group(function () {

    // Admin + Manager + Team Leader can view teams
    Route::get('/', [TeamsController::class, 'index'])
        ->middleware('role:admin,manager,team_leader');

    // View single team
    Route::get('/{id}', [TeamsController::class, 'show'])
        ->middleware('role:admin,manager,team_leader');

    // Only admin can create teams
    Route::post('/', [TeamsController::class, 'store'])
        ->middleware('role:admin');

    // Only admin can update teams
    Route::put('/update/{id}', [TeamsController::class, 'update'])
        ->middleware('role:admin');

    // Only admin can delete teams
    Route::delete('/delete/{id}', [TeamsController::class, 'destroy'])
        ->middleware('role:admin');

    // Admin + Manager can assign members
    Route::post('/add-members/{id}', [TeamsController::class, 'addMembers'])
        ->middleware('role:admin,manager');
});