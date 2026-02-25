<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RoleMiddleware;

require __DIR__.'/api/auth.php';
require __DIR__.'/api/employee.php';


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// Route::middleware('auth:sanctum')->post('/auth/logout', [AuthController::class, 'logout']);

// Route::prefix('auth')->group(function () {

//     Route::post('/register', [AuthController::class, 'register']);

    
//     Route::post('/login', [AuthController::class, 'login']);
// });
// Route::middleware([
//     'auth:sanctum',
//     'role:admin'
// ])->group(function () {

//     Route::get('/dashboard', function () {
//         return response()->json(['message' => 'Welcome to Dashboard']);
//     });

//     Route::get('/profile', function () {
//         return response()->json(['message' => 'User Profile']);
//     });

// });