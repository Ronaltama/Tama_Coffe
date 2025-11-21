<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Table;
use Illuminate\Http\Request;

class UserMenuController extends Controller
{
    /**
     * Ambil semua produk dengan kategori (untuk menu user)
     */
    public function getProducts(Request $request)
    {
        try {
            $query = Product::with('category')
                ->where('status', 'available');

            // Filter berdasarkan kategori jika ada
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            $products = $query->get()->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'imageUrl' => $product->image ? asset('storage/' . $product->image) : 'https://images.unsplash.com/photo-1517487881594-2787fef5ebf7?w=400&q=80',
                    'category' => [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                    ],
                    'type' => strtolower($product->category->name) === 'drinks' || strtolower($product->category->name) === 'drink' ? 'drink' : 'food',
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $products
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ambil detail produk by ID
     */
    public function getProductDetail($id)
    {
        try {
            $product = Product::with('category')->find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'imageUrl' => $product->image ? asset('storage/' . $product->image) : 'https://images.unsplash.com/photo-1517487881594-2787fef5ebf7?w=800&q=80',
                    'category' => [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                    ],
                    'status' => $product->status,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ambil semua kategori
     */
    public function getCategories()
    {
        try {
            $categories = Category::all();

            return response()->json([
                'success' => true,
                'data' => $categories
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kategori',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ambil data meja by ID (untuk QR scan)
     */
    public function getTableByQR($qrCode)
    {
        try {
            $table = Table::where('qr_code', $qrCode)->first();

            if (!$table) {
                return response()->json([
                    'success' => false,
                    'message' => 'Meja tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $table->id,
                    'table_number' => $table->table_number,
                    'capacity' => $table->capacity,
                    'status' => $table->status,
                    'qr_code' => $table->qr_code,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data meja',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
