<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Todas las rutas quedan bajo el prefijo /api (ver bootstrap/app.php).
*/

// --- Autenticación ---------------------------------------------------------
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

// --- Productos (lectura pública) -------------------------------------------
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

// --- Productos (escritura solo administradores) -----------------------------
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
});

// --- Órdenes de compra -------------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::get('/payments/{order}/status', [PaymentController::class, 'status']);
});

// --- Webhook de Stripe (público, verificado por firma) -----------------------
Route::post('/payments/webhook', [PaymentController::class, 'webhook']);