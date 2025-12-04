<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Table;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Dashboard SuperAdmin",
 *     description="API untuk dashboard dan statistik SuperAdmin"
 * )
 */
class DashboardSuperController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/superadmin/dashboard",
     *     tags={"Dashboard SuperAdmin"},
     *     summary="Dapatkan statistik dashboard SuperAdmin",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Data dashboard berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="total_users", type="integer", example=10),
     *             @OA\Property(property="total_admins", type="integer", example=3),
     *             @OA\Property(property="total_superadmin", type="integer", example=1),
     *             @OA\Property(property="total_products", type="integer", example=50),
     *             @OA\Property(property="total_tables", type="integer", example=20),
     *             @OA\Property(property="total_orders", type="integer", example=150),
     *             @OA\Property(property="total_revenue", type="number", format="float", example=5000000),
     *             @OA\Property(property="top_products", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="units_sold", type="integer"),
     *                     @OA\Property(property="revenue", type="number")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden - bukan SuperAdmin")
     * )
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
     * @OA\Get(
     *     path="/api/superadmin/sales-data",
     *     tags={"Dashboard SuperAdmin"},
     *     summary="Dapatkan data penjualan berdasarkan periode",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         required=false,
     *         description="Periode: daily, weekly, monthly, product",
     *         @OA\Schema(type="string", enum={"daily", "weekly", "monthly", "product"}, default="daily")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Data penjualan berhasil diambil",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="label", type="string", example="01 Jan"),
     *                 @OA\Property(property="value", type="number", format="float", example=500000)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid period parameter"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
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
     * @OA\Get(
     *     path="/api/superadmin/orders/history",
     *     tags={"Dashboard SuperAdmin"},
     *     summary="Dapatkan riwayat semua order untuk SuperAdmin",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Riwayat order berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="OR0001"),
     *                     @OA\Property(property="customer_name", type="string"),
     *                     @OA\Property(property="total_price", type="number"),
     *                     @OA\Property(property="status", type="string"),
     *                     @OA\Property(property="processed_by", type="string"),
     *                     @OA\Property(property="created_at", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(property="stats", type="object",
     *                 @OA\Property(property="waiting", type="integer"),
     *                 @OA\Property(property="processing", type="integer"),
     *                 @OA\Property(property="completed", type="integer"),
     *                 @OA\Property(property="finishedRevenue", type="number")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
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
