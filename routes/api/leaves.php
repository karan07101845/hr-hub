<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Leaves\LeavesController;

Route::middleware( ['auth:sanctum'])->prefix('leaves')->group(function () {

    /*
    |---------------------------------------
    | Employee Routes
    |---------------------------------------
    */

    Route::post('/apply', [LeavesController::class, 'apply']);
    Route::get('/my-leaves', [LeavesController::class, 'myLeaves']);

});


/*
|---------------------------------------
| Manager / Admin Routes
|---------------------------------------
*/

Route::middleware(['auth:sanctum','role:admin,manager,team_leader'])
    ->prefix('leaves')
    ->group(function () {

        Route::get('/all-leaves', [LeavesController::class, 'allLeaves']);
        Route::get('/summary', [LeavesController::class, 'summary']);
});


/*
|---------------------------------------
| Approvals
|---------------------------------------
*/

Route::middleware(['auth:sanctum','role:admin,manager'])
    ->prefix('leaves')
    ->group(function () {

        Route::post('/update-status/{id}', [LeavesController::class, 'updateStatus']);
});


/*
|---------------------------------------
| Delete
|---------------------------------------
*/

Route::middleware(['auth:sanctum'])->prefix('leaves')->group(function () {

    Route::delete('/delete/{id}', [LeavesController::class, 'destroy']);

});