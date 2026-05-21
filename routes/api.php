<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\VehicleController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Auth (pubbliche)
    Route::post('/login', [AuthController::class, 'login']);

    // Rotte protette da Sanctum
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        // Veicoli
        Route::get('/vehicles', [VehicleController::class, 'index']);
        Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show']);
        Route::post('/vehicles', [VehicleController::class, 'store']);
        Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update']);
        Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy']);

        // Prenotazioni
        Route::get('/bookings', [BookingController::class, 'index']);
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::delete('/bookings/{booking}', [BookingController::class, 'destroy']);

        // Manutenzioni
        Route::get('/maintenances', [MaintenanceController::class, 'index']);
        Route::post('/maintenances', [MaintenanceController::class, 'store']);
        Route::patch('/maintenances/{maintenance}/complete', [MaintenanceController::class, 'complete']);
    });
});
