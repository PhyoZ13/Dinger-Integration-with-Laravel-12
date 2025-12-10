<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\ProductController;
use Illuminate\Support\Facades\Route;

// API Version 1
Route::prefix('v1')->group(function () {
    
    // Authentication routes (public)
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    // Public product routes (viewing only)
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    // Payment callback (public - called by Dinger)
    Route::post('/payment/callback', [PaymentController::class, 'dingerCallback']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth routes
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/user', [AuthController::class, 'user']);
        });

        // Product CRUD routes (protected)
        Route::apiResource('products', ProductController::class)->except(['index', 'show']);

        // Order routes
        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'index']);
            Route::post('/', [OrderController::class, 'store']);
            Route::get('/{orderId}', [OrderController::class, 'show']);
        });

        // Payment routes
        Route::prefix('payment')->group(function () {
            Route::post('/token', [PaymentController::class, 'getPaymentToken']);
            Route::get('/order/{orderId}', [PaymentController::class, 'getOrderDetail']);
        });
    });
});

