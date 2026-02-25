<?php
// use Illuminate\Support\Facades\Route;
// use App\Http\Middleware\RoleMiddleware;
Route::prefix('employee')->group(function () {

Route::middleware([
    'auth:sanctum',
    'role:employee'
])->group(function () {

    Route::get('/dashboard', function () {
        return response()->json(['message' => 'Welcome to Dashboard']);
    });

    Route::get('/profile', function () {
        return response()->json(['message' => 'User Profile']);
    });

});

});