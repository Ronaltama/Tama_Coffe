@extends('layouts.superadmin')

@section('title', 'Edit Table')

@section('content')
<div class="max-w-3xl">

    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('superadmin.tables.index') }}" class="text-gray-600 hover:text-gray-800 inline-flex items-center gap-2 mb-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Edit Table</h1>
    </div>

    <!-- Form -->
    <form action="{{ route('superadmin.tables.update', $table) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Table Number/Name</label>
            <input type="text"
                   name="table_number"
                   value="{{ old('table_number', $table->table_number) }}"
                   placeholder="e.g., Table 1 or Patio-5"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                   required>
            @error('table_number')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Capacity</label>
                <input type="number"
                       name="capacity"
                       value="{{ old('capacity', $table->capacity) }}"
                       placeholder="e.g., 4"
                       min="1"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                       required>
                @error('capacity')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Current Status</label>
                <select name="status"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                        required>
                    <option value="">Select a status</option>
                    <option value="available" {{ old('status', $table->status) === 'available' ? 'selected' : '' }}>Available</option>
                    <option value="occupied" {{ old('status', $table->status) === 'occupied' ? 'selected' : '' }}>Occupied</option>
                    <option value="reserved" {{ old('status', $table->status) === 'reserved' ? 'selected' : '' }}>Reserved</option>
                </select>
                @error('status')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex items-center justify-end gap-4 pt-6">
            <a href="{{ route('superadmin.tables.index') }}"
               class="px-6 py-3 text-gray-700 hover:text-gray-900 transition">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-3 bg-amber-700 text-white rounded-lg hover:bg-amber-800 transition">
                Update Table
            </button>
        </div>

    </form>

</div>

@endsection
