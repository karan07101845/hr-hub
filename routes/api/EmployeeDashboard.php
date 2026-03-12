<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Leaves\LeavesController;
use App\Http\Controllers\Api\Attendance\AttendanceController;
use App\Http\Controllers\Api\Employee\EmployeeController;
use App\Http\Controllers\Api\Notice\NoticeController;


Route::prefix('employee')->middleware(['auth:sanctum','role:employee'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [EmployeeController::class,'overview']);

    // Attendance
    Route::get('/attendances/my-report', [AttendanceController::class,'myReport']);

    //Notice 
    Route::get('/notice/{notice_type}', [NoticeController::class, 'getByType'])->name('notice.type');

    // Leave
    Route::post('/leave/apply', [LeavesController::class,'apply']);
    Route::get('/leave/my', [LeavesController::class,'myLeaves']);

     
    // Profile 
    // Route::get('/profile', [EmployeeController::class, 'myProfile']);
    // Route::put('/profile/update', [EmployeeController::class, 'updateMyProfile']);
    

});