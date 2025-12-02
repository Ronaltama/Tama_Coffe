<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Table;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class DashboardSuperController extends Controller
{
    /**
     * GET /api/superadmin/dashboard
     */
    public function index()
    {
        // Calculate total revenue from completed/processing orders
        $totalRevenue = Order::whereIn('status', ['completed', 'processing', 'confirmed', 'paid'])
            ->sum('total_price');

        // Get top 3 selling products
        $topProducts = DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->whereIn('orders.status', ['completed', 'processing', 'confirmed', 'paid'])
            ->selectRaw('
                products.name, 
                SUM(order_details.quantity) as units_sold,
                SUM(order_details.subtotal) as revenue
            ')
            ->groupBy('products.id', 'products.name')
            ->orderBy('revenue', 'desc')
            ->limit(3)
            ->get();

        return response()->json([
            'total_users' => User::count(),
            'total_admins' => User::whereHas('role', fn($q) => $q->where('name', 'admin'))->count(),
            'total_superadmin' => User::whereHas('role', fn($q) => $q->where('name', 'superadmin'))->count(),
            'total_products' => Product::count(),
            'total_tables' => Table::count(),
            'total_orders' => Order::count(),
            'total_revenue' => $totalRevenue,
            'top_products' => $topProducts,
        ]);
    }

    /**
     * GET /api/superadmin/sales-data?period={daily|weekly|monthly|product}
     */
    public function getSalesData()
    {
        $period = request('period', 'daily');

        switch ($period) {
            case 'daily':
                return $this->getDailySales();
            case 'weekly':
                return $this->getWeeklySales();
            case 'monthly':
                return $this->getMonthlySales();
            case 'product':
                return $this->getProductSales();
            default:
                return response()->json(['error' => 'Invalid period'], 400);
        }
    }

    private function getDailySales()
    {
        $sales = Order::whereIn('status', ['completed', 'processing'])
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, SUM(total_price) as total')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Fill missing dates with 0
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $found = $sales->firstWhere('date', $date);
            $data[] = [
                'label' => now()->subDays($i)->format('d M'),
                'value' => $found ? (float) $found->total : 0
            ];
        }

        return response()->json($data);
    }

    private function getWeeklySales()
    {
        $sales = Order::whereIn('status', ['completed', 'processing'])
            ->where('created_at', '>=', now()->subWeeks(8))
            ->selectRaw('YEARWEEK(created_at, 1) as week, SUM(total_price) as total')
            ->groupBy('week')
            ->orderBy('week', 'asc')
            ->get();

        $data = [];
        for ($i = 7; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $weekKey = $weekStart->format('oW');
            $found = $sales->firstWhere('week', $weekKey);
            $data[] = [
                'label' => 'Week ' . $weekStart->format('W'),
                'value' => $found ? (float) $found->total : 0
            ];
        }

        return response()->json($data);
    }

    private function getMonthlySales()
    {
        $sales = Order::whereIn('status', ['completed', 'processing'])
            ->where('created_at', '>=', now()->subMonths(12))
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(total_price) as total')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $found = $sales->first(function ($item) use ($date) {
                return $item->year == $date->year && $item->month == $date->month;
            });
            $data[] = [
                'label' => $date->format('M Y'),
                'value' => $found ? (float) $found->total : 0
            ];
        }

        return response()->json($data);
    }

    private function getProductSales()
    {
        $sales = DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->whereIn('orders.status', ['completed', 'processing'])
            ->selectRaw('products.name as label, SUM(order_details.quantity * order_details.price) as value')
            ->groupBy('products.id', 'products.name')
            ->orderBy('value', 'desc')
            ->limit(10)
            ->get();

        return response()->json($sales);
    }

    /**
     * Get all order history for Superadmin with processed_by info
     */
    public function getOrderHistory()
    {
        // Query orders dengan join ke users untuk mendapatkan nama admin
        $orders = DB::table('orders')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->select(
                'orders.*',
                'users.name as processed_by'
            )
            ->orderBy('orders.created_at', 'desc')
            ->get();

        // Hitung stats
        $stats = [
            'waiting' => $orders->where('status', 'pending')->count(),
            'processing' => $orders->where('status', 'processing')->count(),
            'completed' => $orders->where('status', 'completed')->count(),
            'finishedRevenue' => $orders->where('status', 'completed')->sum('total_price'),
        ];

        return response()->json([
            'success' => true,
            'data' => $orders,
            'stats' => $stats,
        ]);
    }
}
