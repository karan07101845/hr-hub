<?php
 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Notification\NotificationController;
 
 
Route::prefix('notification')->middleware(['auth:sanctum','role:admin'])->group(function () {
 
Route::post('/',[NotificationController::class,'addNotification']);
 
Route::post('/update/{id}',[NotificationController::class,'updateNotification']);
 
 
});