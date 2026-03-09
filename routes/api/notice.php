<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Notice\NoticeController;

/*
|--------------------------------------------------------------------------
| Notice Routes
|--------------------------------------------------------------------------
*/

Route::prefix('notice')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/list', [NoticeController::class, 'list'])->name('notice.list');

    Route::post('/', [NoticeController::class, 'store'])->name('notice.store');

    // Route::get('/edit/{id}', [NoticeController::class, 'edit'])->name('notice.edit');

    Route::put('/{id}', [NoticeController::class, 'update'])->name('notice.update');

    Route::delete('/delete/{id}', [NoticeController::class, 'destroy'])->name('notice.delete');

    Route::patch('/status/toggle/{id}', [NoticeController::class, 'toggle'])->name('notice.toggle');

    Route::get('/type/{notice_type}', [NoticeController::class, 'getByType'])->name('notice.type');;

    Route::get('/{id}', [NoticeController::class, 'show'])->name('notice.show');
});
