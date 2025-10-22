@extends('layouts.superadmin')

@section('title', 'Products Management')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Products Management</h1>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200">
        <div class="flex gap-8">
            <a href="{{ route('superadmin.products.index') }}?category=drink"
               class="pb-3 {{ (!request('category') || request('category') === 'drink') ? 'border-b-2 border-amber-700 text-amber-700' : 'text-gray-600' }} font-semibold">
                Drink
            </a>
            <a href="{{ route('superadmin.products.index') }}?category=food"
               class="pb-3 {{ request('category') === 'food' ? 'border-b-2 border-amber-700 text-amber-700' : 'text-gray-600' }} font-semibold">
                Food
            </a>
        </div>
    </div>    <!-- Filters -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex-1">
            <form action="{{ route('superadmin.products.index') }}" method="GET" class="flex gap-4">
                <div class="flex-1 max-w-md">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search {{ request('category') === 'food' ? 'Food' : 'Drink' }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                </div>
                <input type="hidden" name="category" value="{{ request('category', 'drink') }}">
                <button type="submit" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                    Search
                </button>
            </form>
        </div>

        <div class="flex gap-4">
            <select onchange="filterByCategory(this.value)"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('specific_category') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
                @endforeach
            </select>

            <a href="{{ route('superadmin.products.create') }}"
               class="px-6 py-2 bg-amber-700 text-white rounded-lg hover:bg-amber-800 transition flex items-center gap-2">
                <i class="fas fa-plus"></i>
                Add Products
            </a>
        </div>
    </div>

    <!-- Products Table -->
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">Products Name</th>
                    <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">Category</th>
                    <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">Status</th>
                    <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">Price</th>
                    <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($products as $product)
                <tr class="hover:bg-gray-50">
                    <td class="py-4 px-6 text-sm text-gray-900">{{ $product->name }}</td>
                    <td class="py-4 px-6 text-sm text-gray-900">{{ $product->category->name ?? '-' }}</td>
                    <td class="py-4 px-6">
                        <span class="inline-flex items-center text-sm {{ $product->status === 'available' ? 'text-green-600' : 'text-red-600' }}">
                            <span class="mr-1">●</span>
                            {{ ucfirst($product->status) }}
                        </span>
                    </td>
                    <td class="py-4 px-6 text-sm text-gray-900">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                    <td class="py-4 px-6">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('superadmin.products.edit', $product) }}"
                               class="text-amber-600 hover:text-amber-700">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('superadmin.products.destroy', $product) }}"
                                  method="POST"
                                  onsubmit="return confirm('Are you sure you want to delete this product?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-700">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-8 text-center text-sm text-gray-500">No products found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($products->hasPages())
    <div class="flex justify-center">
        {{ $products->links() }}
    </div>
    @endif

</div>

<script>
function filterByCategory(categoryId) {
    const currentUrl = new URL(window.location.href);
    const currentTab = currentUrl.searchParams.get('category') || 'drink';

    if (categoryId) {
        window.location.href = `{{ route('superadmin.products.index') }}?category=${currentTab}&specific_category=${categoryId}`;
    } else {
        window.location.href = `{{ route('superadmin.products.index') }}?category=${currentTab}`;
    }
}
</script>
@endsection
