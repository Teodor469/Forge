<?php

use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('transaction')->group(function() {
    Route::middleware('auth:sanctum')->group(function() {
        Route::get('/user-transactions/list', [TransactionController::class, 'index'])->name('transaction.index');
        Route::get('/user-transactions/list/{category}', [TransactionController::class, 'transactionsPerCategory'])->name('transaction.transactionsPerCategory');
        Route::post('/user-transactions/store', [TransactionController::class, 'store'])->name('transaction.store');
        Route::get('/user-transactions/{transaction}', [TransactionController::class, 'show'])->name('transaction.show');
        Route::put('/user-transactions/update/{transaction}', [TransactionController::class, 'update'])->name('transaction.update');
        Route::delete('/user-transactions/delete/{transaction}', [TransactionController::class, 'delete'])->name('transaction.index');
    });
});