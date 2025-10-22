@extends('layouts.superadmin')

@section('title', 'Edit User')

@section('content')
<div class="max-w-3xl">

    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('superadmin.users.index') }}" class="text-gray-600 hover:text-gray-800 inline-flex items-center gap-2 mb-4">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Edit User</h1>
    </div>

    <!-- Form -->
    <form action="{{ route('superadmin.users.update', $user) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
            <input type="text"
                   name="name"
                   value="{{ old('name', $user->name) }}"
                   placeholder="e.g., Rijal Min Basa"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                   required>
            @error('name')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
            <input type="text"
                   name="username"
                   value="{{ old('username', $user->username) }}"
                   placeholder="e.g., Rijal Cilacap"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                   required>
            @error('username')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
            <input type="email"
                   name="email"
                   value="{{ old('email', $user->email) }}"
                   placeholder="e.g., RijulMinbc@yahoo.com"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                   required>
            @error('email')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input type="password"
                       name="password"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                       placeholder="Leave blank to keep current">
                <p class="mt-1 text-xs text-gray-500">Only fill if you want to change password</p>
                @error('password')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                <input type="password"
                       name="password_confirmation"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                       placeholder="Confirm new password">
            </div>
        </div>

        <div class="flex items-center justify-end gap-4 pt-6">
            <a href="{{ route('superadmin.users.index') }}"
               class="px-6 py-3 text-gray-700 hover:text-gray-900 transition">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-3 bg-amber-700 text-white rounded-lg hover:bg-amber-800 transition">
                Update User
            </button>
        </div>

    </form>

</div>

@endsection
