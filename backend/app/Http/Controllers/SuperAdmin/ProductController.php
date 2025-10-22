<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $categoryType = $request->get('category', 'drink'); // Default to drink
        $search = $request->get('search');
        $specificCategory = $request->get('specific_category'); // Filter specific category

        $query = Product::with('category');

        // Filter berdasarkan tipe category (drink atau food)
        if ($categoryType === 'drink') {
            // Category untuk drink: Coffee, Non-Coffee
            $query->whereHas('category', function($q) {
                $q->whereIn('name', ['Coffee', 'Non-Coffee']);
            });
        } elseif ($categoryType === 'food') {
            // Category untuk food: Snack, Main Course, Dessert
            $query->whereHas('category', function($q) {
                $q->whereIn('name', ['Snack', 'Main Course', 'Dessert']);
            });
        }

        // Filter specific category (dari dropdown)
        if ($specificCategory) {
            $query->where('category_id', $specificCategory);
        }

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $products = $query->latest()->paginate(10);
        $categories = Category::all();

        return view('superadmin.products.index', compact('products', 'categories', 'categoryType'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('superadmin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $validated['status'] = 'available';

        $product = Product::create($validated);

        // Detect category type untuk redirect
        $category = Category::find($validated['category_id']);
        $categoryType = 'drink'; // default

        if ($category && in_array($category->name, ['Snack', 'Main Course', 'Dessert'])) {
            $categoryType = 'food';
        }

        return redirect()->route('superadmin.products.index', ['category' => $categoryType])
            ->with('success', 'Product created successfully!');
    }    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('superadmin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:available,unavailable',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        // Detect category type untuk redirect
        $category = Category::find($validated['category_id']);
        $categoryType = 'drink'; // default

        if ($category && in_array($category->name, ['Snack', 'Main Course', 'Dessert'])) {
            $categoryType = 'food';
        }

        return redirect()->route('superadmin.products.index', ['category' => $categoryType])
            ->with('success', 'Product updated successfully!');
    }    public function destroy(Product $product)
    {
        // Save category info before delete
        $category = $product->category;
        $categoryType = 'drink'; // default

        if ($category && in_array($category->name, ['Snack', 'Main Course', 'Dessert'])) {
            $categoryType = 'food';
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('superadmin.products.index', ['category' => $categoryType])
            ->with('success', 'Product deleted successfully!');
    }
}
