<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\Employee\EmployeeController;

Route::prefix('employee')->group(function () {

    // Authenticated employee routes (role: employee)
    Route::middleware(['auth:sanctum', 'role:employee'])->group(function () {
        Route::get('/dashboard', function () {

            return response()->json(['message' => 'Welcome to Dashboard']);
        });

        Route::get('/profile', function () {
            return response()->json(['message' => 'User Profile']);
        });
    });

    // CRUD + soft delete + restore (roles: admin, manager, employee, team_leader, sales)
    Route::middleware(['auth:sanctum', 'role:admin,manager,employee,team_leader,sales'])->group(function () {

        // Static routes first
        Route::get('/list', [EmployeeController::class, 'getAllEmployees'])->name('employees.list');
        Route::get('/data', [EmployeeController::class, 'employeesData'])->name('employees.data');
        Route::get('/soft/data', [EmployeeController::class, 'getSoftDeletedEmployees'])->name('employees.deletedData');

        // Dynamic routes after static routes
        Route::get('/{id}', [EmployeeController::class, 'getEmployee'])->name('employees.get');
        Route::put('/{id}', [EmployeeController::class, 'updateEmployee'])->name('employees.update');
        Route::delete('/{id}', [EmployeeController::class, 'deleteEmployee'])->name('employees.delete');
        Route::delete('/soft/{id}', [EmployeeController::class, 'softDeleteEmployee'])->name('employees.softdelete');
        Route::delete('/delete/{id}', [EmployeeController::class, 'permanentDeleteEmployee'])->name('employees.permanentDelete');

        Route::post('/restore/{id}', [EmployeeController::class, 'restoreEmployee'])->name('employees.restore');
        Route::post('/bulk-update', [EmployeeController::class, 'bulkUpdateRoles'])->name('employees.bulkUpdate');
    });
});
