<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TeamLeader\TeamLeaderController;
use App\Http\Controllers\Api\Attendance\AttendanceController;
use App\Http\Controllers\Api\Notice\NoticeController;
use App\Http\Controllers\Api\Leaves\LeavesController;


Route::middleware(['auth:sanctum', 'role:team_leader'])
    ->prefix('team-leader')
    ->group(function () {

        Route::get('/dashboard', [TeamLeaderController::class, 'overview']);

        // Attendance
        Route::get('/attendances/my-report', [AttendanceController::class, 'myReport']);

        //Notice 
        Route::get('/notice/{notice_type}', [NoticeController::class, 'getByType'])->name('notice.type');

        // Leave
        Route::post('/leave/apply', [LeavesController::class, 'apply']);
        Route::get('/leave/my', [LeavesController::class, 'myLeaves']);

        // Team
        Route::get('/team-members', [TeamLeaderController::class, 'teamMembers']);
        Route::get('/team-attendance', [TeamLeaderController::class, 'teamAttendance']);

        // Performance
        Route::get('/reviews', [TeamLeaderController::class, 'getReviews']);
        Route::post('/reviews/add', [TeamLeaderController::class, 'addReview']);
        Route::delete('/reviews/delete/{id}', [TeamLeaderController::class, 'deleteReview']);
        Route::patch('/reviews/update/{id}', [TeamLeaderController::class, 'updateReview']);

        //profile
        Route::get('/profile', [TeamLeaderController::class, 'profile']);

        // Change Password
        Route::post('/change-password', [TeamLeaderController::class, 'changePassword']);

        // Logout
        Route::post('/logout', [TeamLeaderController::class, 'logout']);
    });
