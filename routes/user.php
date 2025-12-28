<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('user')->group(function() {
    Route::middleware('auth:sanctum')->group(function() {
        Route::put('/change-name/{user}', [UserController::class, 'changeName'])->name('user.change_name');
    });
});