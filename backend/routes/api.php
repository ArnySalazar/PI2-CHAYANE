<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductoController;

// Rutas públicas
Route::post('/login', [AuthController::class, 'login']);

Route::get('/test', function () {
    return response()->json([
        'message' => 'API funcionando correctamente',
        'timestamp' => now()
    ]);
});

// TEMPORAL: Para probar SIN autenticación
Route::get('/productos', [ProductoController::class, 'index']);
Route::post('/productos', [ProductoController::class, 'store']);
Route::get('/productos/{id}', [ProductoController::class, 'show']);
Route::put('/productos/{id}', [ProductoController::class, 'update']);
Route::delete('/productos/{id}', [ProductoController::class, 'destroy']);
Route::get('/categorias', [ProductoController::class, 'categorias']);

// Rutas protegidas (vacío por ahora)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});