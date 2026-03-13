<?php
 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SalesManager\SalesManagerController;
 
Route::prefix('sales')
    ->middleware(['auth:sanctum', 'role:admin,sales'])
    ->group(function () {

       /*
|---------------------------------------
| SalesManager / Leaves Routes
|---------------------------------------
*/
    Route::get('/team-leaves', [SalesManagerController::class, 'allTeamLeaves']);

    Route::post('/apply-leave', [SalesManagerController::class, 'sendLeaves']);

    Route::get('/leave-summary', [SalesManagerController::class, 'showSummary']);

    Route::get('/team-attendance', [SalesManagerController::class, 'getTeamAttendance']);

    Route::get('/yesterday-status', [SalesManagerController::class, 'yestStatus']);

    Route::get('/team-member/{id}', [SalesManagerController::class, 'showTeamMember']);

    Route::get('/notice/{notice_type}', [SalesManagerController::class, 'ShowNot']);

    Route::get('/public-notice', [SalesManagerController::class, 'ShowPublic']);

    Route::get('/overview', [SalesManagerController::class, 'getOverview']);

});    