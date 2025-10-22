@extends('layouts.superadmin')

@section('title', 'User Management')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div>
        <h1 class="text-3xl font-bold text-gray-900">User Management</h1>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200">
        <div class="flex gap-8">
            <a href="{{ route('superadmin.users.index') }}?tab=admin"
               class="pb-3 {{ $tab === 'admin' ? 'border-b-2 border-amber-700 text-amber-700' : 'text-gray-600' }} font-semibold">
                Admin Users
            </a>
            <a href="{{ route('superadmin.tables.index') }}"
               class="pb-3 {{ request()->routeIs('superadmin.tables.*') ? 'border-b-2 border-amber-700 text-amber-700' : 'text-gray-600' }} font-semibold">
                Tables
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex-1">
            <form action="{{ route('superadmin.users.index') }}" method="GET" class="flex gap-4">
                <div class="flex-1 max-w-lg">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search users by name or email..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                </div>
                <input type="hidden" name="tab" value="{{ $tab }}">
                <button type="submit" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <div class="flex gap-4">
            <select class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                <option>All Roles</option>
                <option value="admin">Admin</option>
                <option value="superadmin">Super Admin</option>
            </select>

            <select class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                <option>Any Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <a href="{{ route('superadmin.users.create') }}"
               class="px-6 py-2 bg-amber-700 text-white rounded-lg hover:bg-amber-800 transition flex items-center gap-2">
                <i class="fas fa-plus"></i>
                Add Admin User
            </a>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">User</th>
                    <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">Role</th>
                    <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">Status</th>
                    <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">Last Login</th>
                    <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="py-4 px-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                <span class="text-sm font-semibold text-gray-600">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                <div class="text-xs text-gray-500">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-6">
                        <span class="inline-block px-3 py-1 bg-pink-100 text-pink-700 text-xs rounded-full">
                            {{ $user->role ? ucfirst($user->role->name) : 'No Role' }}
                        </span>
                    </td>
                    <td class="py-4 px-6">
                        <span class="inline-flex items-center text-sm text-green-600">
                            <span class="mr-1">●</span> Active
                        </span>
                    </td>
                    <td class="py-4 px-6 text-sm text-gray-900">{{ $user->updated_at->diffForHumans() }}</td>
                    <td class="py-4 px-6">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('superadmin.users.edit', $user) }}"
                               class="text-amber-600 hover:text-amber-700">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if(!$user->isSuperAdmin())
                            <form action="{{ route('superadmin.users.destroy', $user) }}"
                                  method="POST"
                                  onsubmit="return confirm('Are you sure you want to delete this user?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-700">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-8 text-center text-sm text-gray-500">No users found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($users->hasPages())
    <div class="flex justify-center">
        {{ $users->links() }}
    </div>
    @endif

</div>
@endsection
