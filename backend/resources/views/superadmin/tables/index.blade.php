@extends('layouts.superadmin')

@section('title', 'Tables Management')

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
               class="pb-3 text-gray-600 font-semibold">
                Admin Users
            </a>
            <a href="{{ route('superadmin.tables.index') }}"
               class="pb-3 border-b-2 border-amber-700 text-amber-700 font-semibold">
                Tables
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex-1">
            <form action="{{ route('superadmin.tables.index') }}" method="GET" class="flex gap-4">
                <div class="flex-1 max-w-lg">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search Tables"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                </div>
                <button type="submit" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <div>
            <a href="{{ route('superadmin.tables.create') }}"
               class="px-6 py-2 bg-amber-700 text-white rounded-lg hover:bg-amber-800 transition flex items-center gap-2">
                <i class="fas fa-plus"></i>
                Add Tables
            </a>
        </div>
    </div>

    <!-- Tables Table -->
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">Tables Number</th>
                    <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">Capacity</th>
                    <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">Status</th>
                    <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">QR-Code</th>
                    <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($tables as $table)
                <tr class="hover:bg-gray-50">
                    <td class="py-4 px-6 text-sm font-medium text-gray-900">{{ $table->table_number }}</td>
                    <td class="py-4 px-6 text-sm text-gray-900">{{ $table->capacity }} People</td>
                    <td class="py-4 px-6">
                        @if($table->status === 'available')
                            <span class="inline-flex items-center text-sm text-green-600">
                                <span class="mr-1">●</span> Available
                            </span>
                        @elseif($table->status === 'occupied')
                            <span class="inline-flex items-center text-sm text-red-600">
                                <span class="mr-1">●</span> Occupied
                            </span>
                        @else
                            <span class="inline-flex items-center text-sm text-yellow-600">
                                <span class="mr-1">●</span> Reserved
                            </span>
                        @endif
                    </td>
                    <td class="py-4 px-6">
                        @if($table->qr_code_url)
                            <div class="w-10 h-10 bg-gray-100 rounded flex items-center justify-center">
                                <i class="fas fa-qrcode text-xl text-gray-600"></i>
                            </div>
                        @else
                            <span class="text-sm text-gray-400">No QR</span>
                        @endif
                    </td>
                    <td class="py-4 px-6">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('superadmin.tables.edit', $table) }}"
                               class="text-amber-600 hover:text-amber-700">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('superadmin.tables.destroy', $table) }}"
                                  method="POST"
                                  onsubmit="return confirm('Are you sure you want to delete this table?')">
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
                    <td colspan="5" class="py-8 text-center text-sm text-gray-500">No tables found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($tables->hasPages())
    <div class="flex justify-center">
        {{ $tables->links() }}
    </div>
    @endif

</div>
@endsection
