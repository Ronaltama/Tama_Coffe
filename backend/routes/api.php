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
use App\Http\Controllers\Api\ManualOrderController;
use App\Http\Controllers\Api\MidtransController;
use Illuminate\Support\Facades\Route;

// =============================================
// 🔓 GUEST ROUTES  (Untuk User/Customer)
// =============================================
Route::prefix('guest')->group(function () {
    Route::get('products', [UserMenuController::class, 'getProducts']);
    Route::get('products/{id}', [UserMenuController::class, 'getProductDetail']);
    Route::get('categories', [UserMenuController::class, 'getCategories']);
    Route::get('table-info/{tableId}', [OrderController::class, 'getTableInfo']);
    Route::get('tables', [TableController::class, 'index']);
    Route::post('orders', [OrderController::class, 'submitOrder']);
    Route::post('reservations', [\App\Http\Controllers\Api\ReservationController::class, 'store']);
    Route::get('reservations/{id}/status', [\App\Http\Controllers\Api\ReservationController::class, 'getStatus']);
    Route::post('midtrans/create-snap-token', [MidtransController::class, 'createSnapToken']);
    Route::post('midtrans/notification', [MidtransController::class, 'notification']);
    Route::get('midtrans/check-status', [MidtransController::class, 'checkStatus']);
    Route::delete('orders/{orderId}/cancel', [OrderController::class, 'cancelOrder']);
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

    // ✅ Routes accessible by both Admin & SuperAdmin (no role check)
    Route::get('orders/history', [ProcessOrderController::class, 'getOrderHistory']);
    Route::get('orders/{id}/detail', [ProcessOrderController::class, 'getOrderDetail']);

    // =============================================
    // 👑 SUPERADMIN ROUTES (Role: RL001)
    // =============================================
    Route::middleware('role:RL001')->group(function () {
        Route::get('/superadmin/dashboard', [DashboardSuperController::class, 'index']);
        Route::get('/superadmin/sales-data', [DashboardSuperController::class, 'getSalesData']);
        Route::get('/superadmin/orders/history', [DashboardSuperController::class, 'getOrderHistory']);

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
        Route::post('orders/{id}/process-payment', [ProcessOrderController::class, 'processPayment']);
        Route::patch('orders/{id}/status', [ProcessOrderController::class, 'updateStatus']);
        Route::delete('orders/{id}', [ProcessOrderController::class, 'deleteOrder']);

        // Dashboard & Stats
        Route::get('dashboard/stats', [ProcessOrderController::class, 'getDashboardStats']);

        // Manual Order Routes
        Route::post('orders/manual', [ManualOrderController::class, 'createManualOrder']);
        Route::get('orders/manual/available-tables', [ManualOrderController::class, 'getAvailableTables']);

        Route::get('admin-data', function () {
            return "Data admin";
        });
    });
});

// Reservation Routes (Public - No Auth)
Route::get('/reservations/availability', [\App\Http\Controllers\Api\ReservationController::class, 'checkAvailability']);
Route::get('/admin/reservations', [\App\Http\Controllers\Api\ReservationController::class, 'index']);
