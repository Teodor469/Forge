<?php

use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('category')->group(function() {
    Route::middleware('auth:sanctum')->group(function() {
        Route::get('/user-categories', [CategoryController::class, 'index'])->name('category.index');
        Route::post('/user-categories/store', [CategoryController::class, 'store'])->name('category.store');
        Route::get('/user-categories/{category}', [CategoryController::class, 'show'])->name('category.show');
        Route::put('/user-categories/update/{category}', [CategoryController::class, 'update'])->name('category.update');
        Route::delete('/user-categories/delete/{category}', [CategoryController::class, 'delete'])->name('category.delete');
    });
});