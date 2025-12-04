<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Table;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Guest Menu",
 *     description="API untuk customer/guest mengakses menu dan detail produk"
 * )
 */
class UserMenuController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/guest/products",
     *     tags={"Guest Menu"},
     *     summary="Dapatkan semua produk yang tersedia",
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", example="CAT001")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List produk berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="PR001"),
     *                     @OA\Property(property="name", type="string", example="Espresso"),
     *                     @OA\Property(property="price", type="number", example=25000),
     *                     @OA\Property(property="imageUrl", type="string"),
     *                     @OA\Property(property="type", type="string", enum={"drink", "food"}),
     *                     @OA\Property(property="category", type="object",
     *                         @OA\Property(property="id", type="string"),
     *                         @OA\Property(property="name", type="string")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Gagal mengambil data produk")
     * )
     */
    public function getProducts(Request $request)
    {
        try {
            $query = Product::with('category')
                ->where('status', 'available');

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
     * @OA\Get(
     *     path="/api/guest/products/{id}",
     *     tags={"Guest Menu"},
     *     summary="Dapatkan detail produk by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", example="PR001")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail produk berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="price", type="number"),
     *                 @OA\Property(property="imageUrl", type="string"),
     *                 @OA\Property(property="category", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Produk tidak ditemukan")
     * )
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
     * @OA\Get(
     *     path="/api/guest/categories",
     *     tags={"Guest Menu"},
     *     summary="Dapatkan semua kategori produk",
     *     @OA\Response(
     *         response=200,
     *         description="List kategori berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string"),
     *                     @OA\Property(property="name", type="string")
     *                 )
     *             )
     *         )
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/guest/table-info/{qrCode}",
     *     tags={"Guest Menu"},
     *     summary="Ambil info meja by QR code",
     *     @OA\Parameter(
     *         name="qrCode",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", example="TB001")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Info meja berhasil diambil"
     *     ),
     *     @OA\Response(response=404, description="Meja tidak ditemukan")
     * )
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
