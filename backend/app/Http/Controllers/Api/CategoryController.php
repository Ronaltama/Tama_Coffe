<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Helpers\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('subCategories')->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'List of categories retrieved successfully',
            'data' => $categories,
        ], 200);
    }

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
