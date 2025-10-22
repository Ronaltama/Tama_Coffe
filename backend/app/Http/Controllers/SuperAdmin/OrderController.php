<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $dateRange = $request->get('date_range');
        $customer = $request->get('customer');

        $query = Order::with(['user', 'table', 'orderDetails.product']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', '%' . $search . '%')
                  ->orWhere('customer_name', 'like', '%' . $search . '%')
                  ->orWhere('customer_phone', 'like', '%' . $search . '%');
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($customer) {
            $query->where('customer_name', 'like', '%' . $customer . '%');
        }

        if ($dateRange) {
            // Parse date range if needed
        }

        $orders = $query->latest()->paginate(15);

        return view('superadmin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'table', 'orderDetails.product', 'payment']);

        return view('superadmin.orders.show', compact('order'));
    }
}
