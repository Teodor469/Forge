<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

require __DIR__ . '/auth.php';

require __DIR__ . '/user.php';

require __DIR__ . '/wallet.php';

require __DIR__ . '/category.php';

require __DIR__ . '/transaction.php';