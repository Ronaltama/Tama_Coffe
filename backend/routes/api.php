<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TableController;

Route::apiResource('categories', CategoryController::class);
Route::apiResource('products', ProductController::class);
// 1. untuk update status produk lewat dropdown
// Route::put('products/{id}/status', [ProductController::class, 'updateStatus']);
//2. untuk update status produk lewat togle on off
Route::patch('products/{id}/toggle-status', [ProductController::class, 'toggleStatus']);

//crud table
Route::apiResource('tables', TableController::class);

// Endpoint untuk menampilkan data meja (ketika QR di-scan)
Route::get('order/{id}', [TableController::class, 'scanOrder']);


