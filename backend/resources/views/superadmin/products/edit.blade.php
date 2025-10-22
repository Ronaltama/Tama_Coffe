@extends('layouts.superadmin')

@section('title', 'Edit Product')

@section('content')
<div class="max-w-3xl">

    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('superadmin.products.index') }}" class="text-gray-600 hover:text-gray-800 inline-flex items-center gap-2 mb-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Edit Product</h1>
    </div>

    <!-- Form -->
    <form action="{{ route('superadmin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Product Name</label>
            <input type="text"
                   name="name"
                   value="{{ old('name', $product->name) }}"
                   placeholder="e.g., Es Kopi Susu"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                   required>
            @error('name')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea name="description"
                      rows="4"
                      placeholder="A short description of the product..."
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">{{ old('description', $product->description) }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Price</label>
            <input type="number"
                   name="price"
                   value="{{ old('price', $product->price) }}"
                   placeholder="Rp 25000"
                   step="0.01"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                   required>
            @error('price')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Product Image</label>

            @if($product->image)
            <div class="mb-3">
                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-32 h-32 object-cover rounded-lg border border-gray-200">
                <p class="text-xs text-gray-500 mt-1">Current image</p>
            </div>
            @endif

            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                <p class="text-sm text-gray-600 mb-2">Upload a new file or drag and drop</p>
                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                <input type="file"
                       name="image"
                       accept="image/*"
                       class="hidden"
                       id="image-upload">
                <label for="image-upload" class="mt-4 inline-block px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 cursor-pointer transition">
                    Choose File
                </label>
            </div>
            @error('image')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
            <select name="category_id"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                    required>
                <option value="">Select category</option>
                @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
                @endforeach
            </select>
            @error('category_id')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select name="status"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                    required>
                <option value="available" {{ old('status', $product->status) === 'available' ? 'selected' : '' }}>Available</option>
                <option value="unavailable" {{ old('status', $product->status) === 'unavailable' ? 'selected' : '' }}>Unavailable</option>
            </select>
            @error('status')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end gap-4 pt-6">
            <a href="{{ route('superadmin.products.index') }}"
               class="px-6 py-3 text-gray-700 hover:text-gray-900 transition">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-3 bg-amber-700 text-white rounded-lg hover:bg-amber-800 transition">
                Update Product
            </button>
        </div>

    </form>

</div>

@push('scripts')
<script>
    document.getElementById('image-upload').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name;
        if (fileName) {
            alert('File selected: ' + fileName);
        }
    });
</script>
@endpush
@endsection
