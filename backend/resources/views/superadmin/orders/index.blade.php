@extends('layouts.superadmin')

@section('title', 'Order History')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Order History</h1>
        <p class="text-sm text-gray-600 mt-1">View and manage all past orders.</p>
    </div>

    <!-- Filters -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex-1">
            <form action="{{ route('superadmin.orders.index') }}" method="GET" class="flex gap-4">
                <div class="flex-1 max-w-lg">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search orders by ID, customer, or product"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                </div>
                <button type="submit" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <div class="flex gap-4">
            <select name="date_range" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                <option>Date Range</option>
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
            </select>

            <select name="customer" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                <option>Customer</option>
            </select>

            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                <option value="">Status</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">ORDER ID</th>
                    <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">DATE</th>
                    <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">CUSTOMER</th>
                    <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">TOTAL AMOUNT</th>
                    <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">STATUS</th>
                    <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">ACTIONS</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($orders as $order)
                <tr class="hover:bg-gray-50">
                    <td class="py-4 px-6 text-sm font-medium text-gray-900">#{{ $order->id }}</td>
                    <td class="py-4 px-6 text-sm text-gray-900">{{ $order->created_at->format('Y-m-d') }}</td>
                    <td class="py-4 px-6 text-sm text-gray-900">{{ $order->customer_name }}</td>
                    <td class="py-4 px-6 text-sm text-gray-900">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                    <td class="py-4 px-6">
                        @if($order->status === 'completed')
                            <span class="inline-block px-3 py-1 bg-green-100 text-green-700 text-xs rounded-full">Completed</span>
                        @elseif($order->status === 'pending')
                            <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-700 text-xs rounded-full">Pending</span>
                        @elseif($order->status === 'processing')
                            <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 text-xs rounded-full">Processing</span>
                        @else
                            <span class="inline-block px-3 py-1 bg-red-100 text-red-700 text-xs rounded-full">{{ ucfirst($order->status) }}</span>
                        @endif
                    </td>
                    <td class="py-4 px-6">
                        <a href="{{ route('superadmin.orders.show', $order) }}"
                           class="text-amber-600 hover:text-amber-700 text-sm">
                            View Details
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-sm text-gray-500">No orders found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($orders->hasPages())
    <div class="flex justify-center">
        {{ $orders->links() }}
    </div>
    @endif

</div>
@endsection
