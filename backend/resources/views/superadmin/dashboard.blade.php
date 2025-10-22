@extends('layouts.superadmin')

@section('title', 'Dashboard SuperAdmin')

@section('content')
<div class="space-y-8">

    <!-- Header -->
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Orders -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <div class="text-sm text-gray-600 mb-2">Total Orders</div>
            <div class="text-3xl font-bold text-gray-900">{{ number_format($totalOrders) }}</div>
        </div>

        <!-- Active Users -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <div class="text-sm text-gray-600 mb-2">Active Users</div>
            <div class="text-3xl font-bold text-gray-900">{{ number_format($activeUsers) }}</div>
        </div>

        <!-- Revenue -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <div class="text-sm text-gray-600 mb-2">Revenue</div>
            <div class="text-3xl font-bold text-gray-900">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Sales Reports -->
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-900">Sales Reports</h2>
            <div class="flex gap-2">
                <button class="px-4 py-2 bg-amber-700 text-white text-sm rounded-lg hover:bg-amber-800 transition">Daily</button>
                <button class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 transition">Weekly</button>
                <button class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 transition">Monthly</button>
                <button class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 transition">Per Product</button>
            </div>
        </div>

        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Daily Sales</h3>
            <p class="text-sm text-gray-600">August 16, 2023</p>
            <p class="text-right text-sm text-gray-600 mt-2">Total Revenue: <span class="font-semibold">Rp {{ number_format($dailySales, 0, ',', '.') }}</span></p>
        </div>

        <div class="bg-gray-100 rounded-lg h-64 flex items-center justify-center">
            <p class="text-gray-500">Sales chart will be displayed here.</p>
        </div>

        <!-- Top Selling Products -->
        <div class="mt-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Selling Products Today</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Product</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Units Sold</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topProducts as $product)
                        <tr class="border-b border-gray-100">
                            <td class="py-3 px-4 text-sm text-gray-900">{{ $product->product }}</td>
                            <td class="py-3 px-4 text-sm text-gray-900">{{ $product->units_sold }}</td>
                            <td class="py-3 px-4 text-sm text-gray-900">Rp {{ number_format($product->revenue, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-6 text-center text-sm text-gray-500">No sales data for today</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- User Management -->
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
        <h2 class="text-xl font-bold text-gray-900 mb-6">User Management</h2>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Name</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Email</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Role</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAdmins as $admin)
                    <tr class="border-b border-gray-100">
                        <td class="py-3 px-4 text-sm text-gray-900">{{ $admin->name }}</td>
                        <td class="py-3 px-4 text-sm text-gray-900">{{ $admin->email }}</td>
                        <td class="py-3 px-4">
                            <span class="inline-block px-3 py-1 bg-pink-100 text-pink-700 text-xs rounded-full">Admin</span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-block text-green-600 text-sm">● Active</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-sm text-gray-500">No admin users found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
