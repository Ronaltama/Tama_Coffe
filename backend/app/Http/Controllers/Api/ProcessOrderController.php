<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Orders",
 *     description="API untuk manajemen order"
 * )
 */
class ProcessOrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/orders/board",
     *     tags={"Orders"},
     *     summary="Get all orders grouped by status (Kanban board)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Order board berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="waiting", type="array",
     *                     @OA\Items(type="object")
     *                 ),
     *                 @OA\Property(property="processing", type="array",
     *                     @OA\Items(type="object")
     *                 ),
     *                 @OA\Property(property="finished", type="array",
     *                     @OA\Items(type="object")
     *                 ),
     *                 @OA\Property(property="cancelled", type="array",
     *                     @OA\Items(type="object")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Gagal mengambil data order")
     * )
     */
    public function getOrderBoard()
    {
        try {
            $today = now()->toDateString();

            $orders = DB::table('orders')
                ->leftJoin('tables', 'orders.table_id', '=', 'tables.id')
                ->leftJoin('payments', 'orders.id', '=', 'payments.order_id')
                ->leftJoin('reservations', 'orders.id', '=', 'reservations.order_id')
                ->select(
                    'orders.*',
                    'tables.table_number',
                    'payments.method as payment_method',
                    'payments.payment_type',
                    'payments.status as payment_status',
                    'reservations.date as reservation_date'
                )
                ->where(function ($query) use ($today) {
                    // Include non-reservation orders created today
                    $query->whereNull('reservations.id')
                        ->whereDate('orders.created_at', $today);

                    // OR include reservation orders only on their reservation date
                    $query->orWhere(function ($subQuery) use ($today) {
                        $subQuery->whereNotNull('reservations.id')
                            ->whereDate('reservations.date', $today);
                    });
                })
                ->orderBy('orders.created_at', 'desc')
                ->get();

            // Group by status
            $grouped = [
                'waiting' => $orders->where('status', 'pending')->values(),
                'processing' => $orders->where('status', 'processing')->values(),
                'finished' => $orders->where('status', 'completed')->values(),
                'cancelled' => $orders->where('status', 'cancelled')->values(),
            ];

            return response()->json([
                'success' => true,
                'data' => $grouped
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/orders/{id}/detail",
     *     tags={"Orders"},
     *     summary="Get order detail by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="string", example="OR001")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail order berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="order", type="object"),
     *                 @OA\Property(property="items", type="array",
     *                     @OA\Items(type="object")
     *                 ),
     *                 @OA\Property(property="payment", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Order tidak ditemukan"),
     *     @OA\Response(response=500, description="Gagal mengambil detail order")
     * )
     */
    public function getOrderDetail($id)
    {
        try {
            // Get order info
            $order = DB::table('orders')
                ->leftJoin('tables', 'orders.table_id', '=', 'tables.id')
                ->leftJoin('users', 'orders.user_id', '=', 'users.id')
                ->select(
                    'orders.*',
                    'tables.table_number',
                    'users.name as processed_by'
                )
                ->where('orders.id', $id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order tidak ditemukan'
                ], 404);
            }

            // Get order items
            $items = DB::table('order_details')
                ->join('products', 'order_details.product_id', '=', 'products.id')
                ->select(
                    'order_details.*',
                    'products.name as product_name',
                    'products.image as product_image'
                )
                ->where('order_details.order_id', $id)
                ->get();

            // Get payment info
            $payment = DB::table('payments')
                ->where('order_id', $id)
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'order' => $order,
                    'items' => $items,
                    'payment' => $payment
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/orders/{id}/process-payment",
     *     tags={"Orders"},
     *     summary="Process payment for cash orders",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="string", example="OR001")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount_paid"},
     *             @OA\Property(property="amount_paid", type="number", example=100000, description="Jumlah uang yang dibayarkan")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pembayaran berhasil diproses",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total", type="number"),
     *                 @OA\Property(property="paid", type="number"),
     *                 @OA\Property(property="change", type="number")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Order tidak ditemukan"),
     *     @OA\Response(response=400, description="Jumlah pembayaran kurang"),
     *     @OA\Response(response=500, description="Gagal memproses pembayaran")
     * )
     */
    public function processPayment(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'amount_paid' => 'required|numeric|min:0'
            ]);

            DB::beginTransaction();

            $order = DB::table('orders')->where('id', $id)->first();
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order tidak ditemukan'
                ], 404);
            }

            // Calculate change
            $change = $validated['amount_paid'] - $order->total_price;

            if ($change < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah pembayaran kurang'
                ], 400);
            }

            // Update payment status
            DB::table('payments')
                ->where('order_id', $id)
                ->update([
                    'status' => 'paid',
                    'updated_at' => now()
                ]);

            // Update order status to processing
            DB::table('orders')
                ->where('id', $id)
                ->update([
                    'status' => 'processing',
                    'updated_at' => now()
                ]);

            // Update table status to occupied if dine-in order
            if ($order->table_id) {
                DB::table('tables')
                    ->where('id', $order->table_id)
                    ->update([
                        'status' => 'occupied',
                        'updated_at' => now()
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil diproses',
                'data' => [
                    'total' => $order->total_price,
                    'paid' => $validated['amount_paid'],
                    'change' => $change
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/orders/{id}/status",
     *     tags={"Orders"},
     *     summary="Update order status",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="string", example="OR001")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"pending", "processing", "completed", "cancelled"}, example="processing")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status order berhasil diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="order_id", type="string"),
     *                 @OA\Property(property="status", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Order tidak ditemukan"),
     *     @OA\Response(response=500, description="Gagal mengupdate status")
     * )
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:pending,processing,completed,cancelled'
            ]);

            DB::beginTransaction();

            $order = DB::table('orders')->where('id', $id)->first();
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order tidak ditemukan'
                ], 404);
            }

            // Update status order
            DB::table('orders')
                ->where('id', $id)
                ->update([
                    'status' => $validated['status'],
                    'updated_at' => now()
                ]);

            // Jika order selesai → meja jadi available
            if ($validated['status'] === 'completed' && $order->table_id) {
                DB::table('tables')
                    ->where('id', $order->table_id)
                    ->update(['status' => 'available']);
            }

            // Jika order dibatalkan → meja juga harus available
            if ($validated['status'] === 'cancelled' && $order->table_id) {
                DB::table('tables')
                    ->where('id', $order->table_id)
                    ->update(['status' => 'available']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status order berhasil diupdate',
                'data' => [
                    'order_id' => $id,
                    'status' => $validated['status']
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate status',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * @OA\Delete(
     *     path="/api/orders/{id}",
     *     tags={"Orders"},
     *     summary="Delete cancelled order",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="string", example="OR001")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order berhasil dihapus",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Order tidak ditemukan"),
     *     @OA\Response(response=400, description="Hanya order yang dibatalkan yang bisa dihapus"),
     *     @OA\Response(response=500, description="Gagal menghapus order")
     * )
     */
    public function deleteOrder($id)
    {
        try {
            DB::beginTransaction();

            $order = DB::table('orders')->where('id', $id)->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order tidak ditemukan'
                ], 404);
            }

            // Only allow deletion of cancelled orders
            if ($order->status !== 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya order yang dibatalkan yang bisa dihapus'
                ], 400);
            }

            // Delete order (cascade will handle order_details and payments)
            DB::table('orders')->where('id', $id)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dihapus'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/stats",
     *     tags={"Orders"},
     *     summary="Get dashboard statistics (today's data)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Data dashboard berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="stats", type="object",
     *                     @OA\Property(property="waiting", type="integer", example=5),
     *                     @OA\Property(property="processing", type="integer", example=3),
     *                     @OA\Property(property="completed", type="integer", example=15),
     *                     @OA\Property(property="finishedRevenue", type="number", example=750000)
     *                 ),
     *                 @OA\Property(property="recentOrders", type="array",
     *                     @OA\Items(type="object")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Gagal mengambil data dashboard")
     * )
     */
    public function getDashboardStats()
    {
        try {
            $today = now()->toDateString();

            // Count orders by status (only today)
            $waiting = DB::table('orders')
                ->whereDate('created_at', $today)
                ->where('status', 'pending')
                ->count();

            $processing = DB::table('orders')
                ->whereDate('created_at', $today)
                ->where('status', 'processing')
                ->count();

            $completed = DB::table('orders')
                ->whereDate('created_at', $today)
                ->where('status', 'completed')
                ->count();

            // Calculate total revenue from completed orders (only today)
            $finishedRevenue = DB::table('orders')
                ->whereDate('created_at', $today)
                ->where('status', 'completed')
                ->sum('total_price');

            // Get recent orders (today only, limit 5)
            $recentOrders = DB::table('orders')
                ->leftJoin('tables', 'orders.table_id', '=', 'tables.id')
                ->select(
                    'orders.id',
                    'orders.customer_name',
                    'orders.created_at',
                    'orders.status',
                    'orders.total_price',
                    'tables.table_number'
                )
                ->whereDate('orders.created_at', $today)
                ->orderBy('orders.created_at', 'desc')
                ->limit(5)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => [
                        'waiting' => $waiting,
                        'processing' => $processing,
                        'completed' => $completed,
                        'finishedRevenue' => (float) $finishedRevenue
                    ],
                    'recentOrders' => $recentOrders
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/orders/history",
     *     tags={"Orders"},
     *     summary="Get order history",
     *     description="SuperAdmin (RL001) mendapat semua order. Admin (RL002) hanya order yang dibuat oleh mereka.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="History order berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="stats", type="object",
     *                     @OA\Property(property="waiting", type="integer"),
     *                     @OA\Property(property="processing", type="integer"),
     *                     @OA\Property(property="completed", type="integer"),
     *                     @OA\Property(property="finishedRevenue", type="number")
     *                 ),
     *                 @OA\Property(property="orders", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="string"),
     *                         @OA\Property(property="customer_name", type="string"),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(property="status", type="string"),
     *                         @OA\Property(property="total_price", type="number")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=500, description="Gagal mengambil history")
     * )
     */
    public function getOrderHistory(Request $request)
    {
        $user = Auth::user();

        // Pastikan user punya role yang diizinkan (opsional)
        $roleId = $user->role_id ?? $user->role ?? null;

        // Jika bukan admin/superadmin, tolak akses
        if (!in_array($roleId, ['RL001', 'RL002'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Query orders: jika superadmin ambil semua, jika admin filter by user_id
        $query = Order::query();

        if ($roleId === 'RL002') {
            $query->where('user_id', $user->id);
        }

        // Ambil orders terbaru dulu
        $orders = $query->orderBy('created_at', 'desc')->get();

        // Hitung stats berdasarkan hasil query (bisa disesuaikan)
        $stats = [
            'waiting' => $orders->where('status', 'pending')->count(),
            'processing' => $orders->where('status', 'processing')->count(),
            'completed' => $orders->where('status', 'completed')->count(),
            'finishedRevenue' => $orders->where('status', 'completed')->sum('total_price'),
        ];

        // Format orders untuk frontend (sesuaikan fields jika model beda)
        $ordersArr = $orders->map(function ($o) {
            return [
                'id' => $o->id,
                'customer_name' => $o->customer_name,
                'created_at' => $o->created_at,
                'status' => $o->status,
                'total_price' => $o->total_price,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'orders' => $ordersArr,
            ],
        ]);
    }
}
