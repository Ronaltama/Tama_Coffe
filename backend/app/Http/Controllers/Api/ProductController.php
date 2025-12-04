<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Helpers\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @OA\Tag(
 *     name="Products",
 *     description="API untuk manajemen produk"
 * )
 */
class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     tags={"Products"},
     *     summary="Dapatkan semua produk",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List produk berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="price", type="number"),
     *                     @OA\Property(property="status", type="string", enum={"available", "unavailable"})
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $products = Product::with(['category', 'subCategory'])->latest()->get();
        return response()->json([
            'success' => true,
            'message' => 'List of products retrieved successfully',
            'data' => $products,
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     tags={"Products"},
     *     summary="Dapatkan detail produk",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Detail produk"),
     *     @OA\Response(response=404, description="Produk tidak ditemukan")
     * )
     */
    public function show($id)
    {
        try {
            $product = Product::with(['category', 'subCategory'])->findOrFail($id);
            return response()->json([
                'success' => true,
                'message' => 'Product retrieved successfully',
                'data' => $product,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     tags={"Products"},
     *     summary="Buat produk baru",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"category_id", "name", "price"},
     *             @OA\Property(property="category_id", type="string", example="CT001"),
     *             @OA\Property(property="name", type="string", example="Espresso"),
     *             @OA\Property(property="description", type="string", example="Kopi espresso premium"),
     *             @OA\Property(property="price", type="number", example=25000),
     *             @OA\Property(property="status", type="string", enum={"available", "unavailable"})
     *         )
     *     ),
     *     @OA\Response(response=201, description="Produk berhasil dibuat"),
     *     @OA\Response(response=422, description="Validasi gagal")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'status' => 'in:available,unavailable',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $id = IdGenerator::generate('products', 'PR');
        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('products', 'public')
            : null;

        $product = Product::create([
            'id' => $id,
            'category_id' => $validated['category_id'],
            'sub_category_id' => $validated['sub_category_id'] ?? null,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'image' => $imagePath,
            'price' => $validated['price'],
            'status' => $validated['status'] ?? 'available',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product,
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/products/{id}",
     *     tags={"Products"},
     *     summary="Update produk",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="status", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Produk berhasil diupdate"),
     *     @OA\Response(response=404, description="Produk tidak ditemukan")
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
            $validated = $request->validate([
                'category_id' => 'sometimes|exists:categories,id',
                'sub_category_id' => 'nullable|exists:sub_categories,id',
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'price' => 'sometimes|numeric|min:0',
                'status' => 'in:available,unavailable',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            if ($request->hasFile('image')) {
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                $validated['image'] = $request->file('image')->store('products', 'public');
            }

            $product->update($validated);
            $product->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     tags={"Products"},
     *     summary="Hapus produk",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Produk berhasil dihapus"),
     *     @OA\Response(response=404, description="Produk tidak ditemukan")
     * )
     */
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $product->delete();
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/products/{id}/toggle-status",
     *     tags={"Products"},
     *     summary="Toggle status produk (available/unavailable)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Status produk berhasil diubah"),
     *     @OA\Response(response=404, description="Produk tidak ditemukan")
     * )
     */
    public function toggleStatus($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->status = $product->status === 'available' ? 'unavailable' : 'available';
            $product->save();

            return response()->json([
                'success' => true,
                'message' => 'Product status toggled successfully',
                'data' => $product,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }
    }
}
