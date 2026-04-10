<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Api\Auth\CurrentUserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('auth.')->group(function (): void {
    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:api-login')
        ->name('login');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('me', CurrentUserController::class)->name('me');

        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
            ->name('logout');

        Route::post('logout-all', [AuthenticatedSessionController::class, 'destroyAll'])
            ->name('logout-all');
    });
});
