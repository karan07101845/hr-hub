<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RoleMiddleware;

require __DIR__.'/api/auth.php';
require __DIR__.'/api/employee.php';
require __DIR__ . '/api/leaves.php';
require __DIR__ . '/api/Teams.php';
require __DIR__ . '/api/attendances.php';
require __DIR__ . '/api/notice.php';
require __DIR__ . '/api/admin.php';
require __DIR__ . '/api/EmployeeDashboard.php';
require __DIR__ . '/api/TeamLeaderDashboard.php';
require __DIR__ . '/api/notification.php';
require __DIR__ . '/api/manager.php';




Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


