<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TeamLeader\TeamLeaderController;
use App\Http\Controllers\Api\Attendance\AttendanceController;
use App\Http\Controllers\Api\Notice\NoticeController;
use App\Http\Controllers\Api\Leaves\LeavesController;


Route::middleware(['auth:sanctum','role:team_leader'])
->prefix('team-leader')
->group(function () {

    Route::get('/dashboard', [TeamLeaderController::class,'overview']);

    // Attendance
    Route::get('/attendances/my-report', [AttendanceController::class,'myReport']);

    //Notice 
    Route::get('/notice/{notice_type}', [NoticeController::class, 'getByType'])->name('notice.type');

    // Leave
    Route::post('/leave/apply', [LeavesController::class,'apply']);
    Route::get('/leave/my', [LeavesController::class,'myLeaves']);

    



});
