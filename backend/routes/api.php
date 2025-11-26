<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardSuperController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProcessOrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TableController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserMenuController;
use Illuminate\Support\Facades\Route;

// =============================================
// 🔓 GUEST ROUTES (Untuk User/Customer)
// =============================================
Route::prefix('guest')->group(function () {
    // Ambil semua produk (bisa filter by category)
    Route::get('products', [UserMenuController::class, 'getProducts']);

    // Ambil detail produk by ID
    Route::get('products/{id}', [UserMenuController::class, 'getProductDetail']);

    // Ambil semua kategori
    Route::get('categories', [UserMenuController::class, 'getCategories']);

    // Ambil info meja by ID (untuk scan QR atau simulasi)
    Route::get('table-info/{tableId}', [OrderController::class, 'getTableInfo']);

    // 🆕 Ambil list semua meja (untuk simulasi scan)
    Route::get('tables', [TableController::class, 'index']);

    // Submit order (POST)
    Route::post('orders', [OrderController::class, 'submitOrder']);
});

// =============================================
// 🔐 AUTH ROUTES
// =============================================
Route::post('login', [AuthController::class, 'login']);

// =============================================
// 🔒 AUTHENTICATED ROUTES
// =============================================
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // =============================================
    // 👑 SUPERADMIN ROUTES (Role: RL001)
    // =============================================
    Route::middleware('role:RL001')->group(function () {
        Route::get('/superadmin/dashboard', [DashboardSuperController::class, 'index']);
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('products', ProductController::class);
        Route::patch('products/{id}/toggle-status', [ProductController::class, 'toggleStatus']);
        Route::apiResource('tables', TableController::class);
        Route::apiResource('users', UserController::class);

        Route::get('superadmin-data', function () {
            return "Data superadmin";
        });
    });

    // =============================================
    // 🛠️ ADMIN ROUTES (Role: RL002)
    // =============================================
    Route::middleware('role:RL002')->group(function () {
        // Order Board & Processing
        Route::get('orders/board', [ProcessOrderController::class, 'getOrderBoard']);
        Route::get('orders/{id}/detail', [ProcessOrderController::class, 'getOrderDetail']);
        Route::post('orders/{id}/process-payment', [ProcessOrderController::class, 'processPayment']);
        Route::patch('orders/{id}/status', [ProcessOrderController::class, 'updateStatus']);
        Route::delete('orders/{id}', [ProcessOrderController::class, 'deleteOrder']);

        Route::get('admin-data', function () {
            return "Data admin";
        });
    });
});
