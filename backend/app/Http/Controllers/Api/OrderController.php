<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Table;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrderController extends Controller
{
    /**
     * Scan QR — get table info and available products
     */
    public function scanOrder($id)
    {
        try {
            // 1️⃣ Cari meja berdasarkan ID
            $table = Table::findOrFail($id);

            // 2️⃣ Ambil daftar produk aktif
            $products = Product::where('status', 'available')
                ->orderBy('name')
                ->get();

            // 3️⃣ Response JSON ke frontend
            return response()->json([
                'success' => true,
                'message' => 'Table and products retrieved successfully',
                'data' => [
                    'table' => $table,
                    'products' => $products,
                ],
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Table not found',
            ], 404);
        }
    }
}
