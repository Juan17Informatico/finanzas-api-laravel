<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('register', [\App\Http\Controllers\Api\V1\AuthController::class, 'register']);
    Route::post('login', [\App\Http\Controllers\Api\V1\AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [\App\Http\Controllers\Api\V1\AuthController::class, 'logout']);
        Route::get('/me', [\App\Http\Controllers\Api\V1\AuthController::class, 'me']);

        Route::apiResource('budgets', \App\Http\Controllers\Api\V1\BudgetController::class);
        Route::get('budgets/reports', [\App\Http\Controllers\Api\V1\BudgetController::class, 'reports']);
        Route::apiResource('categories', \App\Http\Controllers\Api\V1\CategoryController::class);
        Route::apiResource('transactions', \App\Http\Controllers\Api\V1\TransactionController::class);
    });
});
