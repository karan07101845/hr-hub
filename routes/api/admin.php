<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\SystemController;

 /*
    |--------------------------------------------------------------------------
    | Systems Routes
    |--------------------------------------------------------------------------
    */

Route::middleware(['auth:sanctum'])->prefix('systems')->group(function () {
    Route::post('/', [SystemController::class, 'store'])->name('systems.store');

    Route::get('/show', [SystemController::class, 'showAllData'])->name('systems.show');

    Route::put('/edit/{id}', [SystemController::class, 'edit'])->name('systems.edit');

    Route::patch('/update/{id}', [SystemController::class, 'updateData'])->name('systems.update');

    Route::delete('/delete/{id}', [SystemController::class, 'deletedata'])->name('systems.delete');
});
