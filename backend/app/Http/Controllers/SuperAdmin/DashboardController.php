<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Total Orders
        $totalOrders = Order::count();

        // Active Users (users with admin role)
        $activeUsers = User::whereHas('role', function($query) {
            $query->where('name', 'admin');
        })->count();

        // Total Revenue
        $totalRevenue = Order::where('status', 'completed')->sum('total_price');

        // Daily Sales
        $dailySales = Order::whereDate('created_at', today())
            ->where('status', 'completed')
            ->sum('total_price');

        // Top Selling Products Today
        $topProducts = DB::table('order_details')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->whereDate('orders.created_at', today())
            ->where('orders.status', 'completed')
            ->select(
                'products.name as product',
                DB::raw('SUM(order_details.quantity) as units_sold'),
                DB::raw('SUM(order_details.subtotal) as revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('units_sold')
            ->limit(3)
            ->get();

        // Recent Admin Users
        $recentAdmins = User::whereHas('role', function($query) {
            $query->where('name', 'admin');
        })
        ->with('role')
        ->latest()
        ->take(5)
        ->get();

        return view('superadmin.dashboard', compact(
            'totalOrders',
            'activeUsers',
            'totalRevenue',
            'dailySales',
            'topProducts',
            'recentAdmins'
        ));
    }
}
