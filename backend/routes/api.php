<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TableController;
use App\Http\Controllers\Api\UserController;

// Endpoint untuk menampilkan data meja (ketika QR di-scan)
Route::get('order/{id}', [TableController::class, 'scanOrder']);


Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    Route::middleware('role:RL001')->group(function () {
        Route::apiResource('categories', CategoryController::class); //crud kategori
        Route::apiResource('products', ProductController::class); //crud produk
        // 1. untuk update status produk lewat dropdown
            // Route::put('products/{id}/status', [ProductController::class, 'updateStatus']);
        //2. untuk update status produk lewat togle on off
        Route::patch('products/{id}/toggle-status', [ProductController::class, 'toggleStatus']);
        Route::apiResource('tables', TableController::class); //crud meja
        Route::apiResource('users', UserController::class); //crud user
        Route::get('superadmin-data', function () {
            return "Data superadmin";
        });
    });

    Route::middleware('role:RL002')->group(function () {
        Route::get('admin-data', function () {
            return "Data admin";
        });
        Route::apiResource('tables', TableController::class);
    });
});
