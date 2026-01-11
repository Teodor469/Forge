<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('wallet')->group(function() {
    Route::middleware('auth:sanctum')->group(function() {
        Route::get('/user-wallets/active', [WalletController::class, 'active'])->name('wallet.active');
        Route::get('/user-wallets/archived', [WalletController::class, 'archived'])->name('wallet.archived');
        Route::post('/user-wallets/store', [WalletController::class, 'store'])->name('wallet.store');
        Route::get('/user-wallets/{wallet}', [WalletController::class, 'show'])->name('wallet.show');
        Route::put('/user-wallets/update/{wallet}', [WalletController::class, 'update'])->name('wallet.update');
        Route::delete('/user-wallets/delete/{wallet}', [WalletController::class, 'delete'])->name('wallet.delete');
    });
});