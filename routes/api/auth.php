<?php
// use Illuminate\Support\Facades\Route;
use  App\Http\Controllers\Api\Auth\AuthController;

Route::middleware('auth:sanctum')->post('/auth/logout', [AuthController::class, 'logout']);

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->put('/update', [AuthController::class, 'updatePassword']);

Route::post('/forgot', [AuthController::class, 'forgotPassword']);
Route::post('/reset', [AuthController::class, 'resetPassword']);

});

Route::post('/test/{id}',[AuthController::class,'register']);
