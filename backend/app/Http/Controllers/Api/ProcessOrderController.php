<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcessOrderController extends Controller
{
    /**
     * Get all orders grouped by status (untuk Kanban board)
     * GET /api/orders/board
     */
    public function getOrderBoard()
    {
        try {
            $orders = DB::table('orders')
                ->leftJoin('tables', 'orders.table_id', '=', 'tables.id')
                ->leftJoin('payments', 'orders.id', '=', 'payments.order_id')
                ->select(
                    'orders.*',
                    'tables.table_number',
                    'payments.method as payment_method',
                    'payments.payment_type',
                    'payments.status as payment_status'
                )
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
     * Get order detail by ID
     * GET /api/orders/{id}/detail
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
     * Process payment for cash orders
     * POST /api/orders/{id}/process-payment
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
     * Update order status
     * PATCH /api/orders/{id}/status
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
     * Delete cancelled order
     * DELETE /api/orders/{id}
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
}
