<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardSuperController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TableController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserMenuController;
use Illuminate\Support\Facades\Route;

// Guest routes (tanpa auth)
Route::prefix('guest')->group(function () {
    Route::get('products', [UserMenuController::class, 'getProducts']);
    Route::get('products/{id}', [UserMenuController::class, 'getProductDetail']);
    Route::get('categories', [UserMenuController::class, 'getCategories']);
    Route::get('table/{qrCode}', [UserMenuController::class, 'getTableByQR']);
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
        Route::get('admin-data', function () {
            return "Data admin";
        });
    });
});
