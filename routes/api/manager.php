<?php
 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Manager\ManagerController;
 
Route::prefix('manager')
    ->middleware(['auth:sanctum', 'role:admin,manager'])
    ->group(function () {
 
    /*
|---------------------------------------
| Manager / Leaves Routes
|---------------------------------------
*/
 
        Route::post('/apply', [ManagerController::class, 'sendLeaves']);
 
        Route::get('/leaves', [ManagerController::class, 'allTeamLeaves']);
 
        Route::get('/summary', [ManagerController::class, 'showSummary']);
 
 
    /*
|---------------------------------------
| Manager / Attendence Routes
|---------------------------------------
*/
 
    Route::get('/Attendance', [ManagerController::class, 'getTeamAttendance']);
 
        Route::get('/last-status', [ManagerController::class, 'yestStatus']);
 
 
 
 
    /*
|---------------------------------------
| Manager / Teams Routes
|---------------------------------------
*/
 
    Route::get('teams/{id}', [ManagerController::class, 'showTeamMember']);
 
 
 
        /*
|---------------------------------------
| Manager / Notice Routes
|---------------------------------------
*/
 
    Route::get('/type/{notice_type}', [ManagerController::class, 'ShowNot']);
 
    Route::get('/onlypublic',[ManagerController::class,'ShowPublic']);
 
 
 
            /*
|---------------------------------------
| Manager / overeviews Routes
|---------------------------------------
*/
Route::get('/overview', [ManagerController::class, 'getOverview']);
 
    });