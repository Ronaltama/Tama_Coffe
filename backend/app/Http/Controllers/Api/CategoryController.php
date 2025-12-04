<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Helpers\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @OA\Tag(
 *     name="Categories",
 *     description="API untuk manajemen kategori produk"
 * )
 */
class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/categories",
     *     tags={"Categories"},
     *     summary="Dapatkan semua kategori",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List kategori berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="List of categories retrieved successfully"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="CT001"),
     *                     @OA\Property(property="name", type="string", example="Coffee")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $categories = Category::with('subCategories')->latest()->get();
        return response()->json([
            'success' => true,
            'message' => 'List of categories retrieved successfully',
            'data' => $categories,
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/categories/{id}",
     *     tags={"Categories"},
     *     summary="Dapatkan detail kategori",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", example="CT001")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail kategori berhasil diambil"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Kategori tidak ditemukan"
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $category = Category::with('subCategories')->findOrFail($id);
            return response()->json([
                'success' => true,
                'message' => 'Category retrieved successfully',
                'data' => $category,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/categories",
     *     tags={"Categories"},
     *     summary="Buat kategori baru",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Coffee")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Kategori berhasil dibuat"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validasi gagal"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        $id = IdGenerator::generate('categories', 'CT');
        $category = Category::create([
            'id' => $id,
            'name' => $validated['name'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => $category,
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/categories/{id}",
     *     tags={"Categories"},
     *     summary="Update kategori",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Coffee Updated")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Kategori berhasil diupdate"),
     *     @OA\Response(response=404, description="Kategori tidak ditemukan")
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $category = Category::findOrFail($id);
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:categories,name,' . $id . ',id',
            ]);
            $category->update($validated);
            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => $category,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/categories/{id}",
     *     tags={"Categories"},
     *     summary="Hapus kategori",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Kategori berhasil dihapus"),
     *     @OA\Response(response=404, description="Kategori tidak ditemukan")
     * )
     */
    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->delete();
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }
    }
}
