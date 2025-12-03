<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Get table info by ID (untuk scan QR atau simulasi)
     * Endpoint: GET /api/guest/table-info/{tableId}
     */
    public function getTableInfo($tableId)
    {
        try {
            $table = Table::find($tableId);

            if (!$table) {
                return response()->json([
                    'success' => false,
                    'message' => 'Meja tidak ditemukan'
                ], 404);
            }

            // Cek status meja
            if ($table->status === 'maintenance') {
                return response()->json([
                    'success' => false,
                    'message' => 'Meja sedang dalam perbaikan',
                    'data' => [
                        'table_number' => $table->table_number,
                        'status' => $table->status
                    ]
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data meja berhasil diambil',
                'data' => [
                    'id' => $table->id,
                    'table_number' => $table->table_number,
                    'capacity' => $table->capacity,
                    'type' => $table->type,
                    'status' => $table->status,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data meja',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit order dari customer (guest)
     * Endpoint: POST /api/guest/orders
     */
    public function submitOrder(Request $request)
    {
        try {
            Log::info('Order Request Received:', $request->all());

            // Validasi input
            $validated = $request->validate([
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'nullable|string|max:20',
                'customer_email' => 'nullable|email',
                'table_id' => 'nullable|string|exists:tables,id',
                'table_number' => 'nullable|string',
                'order_type' => 'required|in:Dine In,Reservasi',
                'payment_method' => 'required|in:cash,qris',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|string|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.variant' => 'nullable|string',
                'items.*.price' => 'required|numeric|min:0',
                // reservation_data untuk create reservasi baru bersamaan dengan order
                'reservation_data' => 'nullable|array',
                'reservation_data.customer_name' => 'required_with:reservation_data|string|max:255',
                'reservation_data.customer_phone' => 'required_with:reservation_data|string|max:20',
                'reservation_data.table_id' => 'required_with:reservation_data|string|exists:tables,id',
                'reservation_data.reservation_date' => 'required_with:reservation_data|date',
                'reservation_data.reservation_time' => 'required_with:reservation_data',
            ]);

            DB::beginTransaction();

            // Check if this is a reservation order - create reservation first
            $reservationId = null;
            if ($validated['order_type'] === 'Reservasi' && !empty($validated['reservation_data'])) {
                // Validasi meja belum direservasi (cukup cek tanggal dan table_id)
                $isReserved = \App\Models\Reservation::where('date', $validated['reservation_data']['reservation_date'])
                    ->where('table_id', $validated['reservation_data']['table_id'])
                    ->exists();

                if ($isReserved) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Meja sudah direservasi pada tanggal tersebut.'
                    ], 422);
                }

                // Generate Reservation ID & Booking Code
                $reservationId = 'RES' . time() . rand(100, 999);
                $bookingCode = 'BOOK' . strtoupper(substr(md5($reservationId), 0, 6));

                Log::info('Creating reservation:', [
                    'id' => $reservationId,
                    'booking_code' => $bookingCode,
                    'data' => $validated['reservation_data']
                ]);

                // Create reservation
                \App\Models\Reservation::create([
                    'id' => $reservationId,
                    'order_id' => null, // Will be updated after order created
                    'name' => $validated['reservation_data']['customer_name'],
                    'phone' => $validated['reservation_data']['customer_phone'],
                    'table_id' => $validated['reservation_data']['table_id'],
                    'date' => $validated['reservation_data']['reservation_date'],
                    'time' => $validated['reservation_data']['reservation_time'],
                    'booking_code' => $bookingCode,
                ]);

                Log::info('Reservation created successfully', ['id' => $reservationId]);
            }

            // For guest orders, no need for user_id
            $defaultUserId = null;

            // --- LOGIKA CREATE ORDER (untuk Dine In atau Reservasi) ---

            // Generate Order ID
            $orderCount = DB::table('orders')->count();
            $orderId = 'OR' . str_pad($orderCount + 1, 4, '0', STR_PAD_LEFT);

            // Hitung total price
            $totalPrice = 0;
            foreach ($validated['items'] as $item) {
                $totalPrice += $item['price'] * $item['quantity'];
            }

            // Buat Order
            DB::table('orders')->insert([
                'id' => $orderId,
                'user_id' => $defaultUserId,
                'table_id' => $validated['table_id'] ?? null,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'] ?? '',
                'total_price' => $totalPrice,
                'note' => $validated['notes'] ?? null,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update reservation dengan order_id jika ini dari reservasi
            if (!empty($reservationId)) {
                DB::table('reservations')
                    ->where('id', $reservationId)
                    ->update(['order_id' => $orderId, 'updated_at' => now()]);
            }

            // Buat Order Details
            foreach ($validated['items'] as $item) {
                $detailCount = DB::table('order_details')->count();
                $detailId = 'OD' . str_pad($detailCount + 1, 4, '0', STR_PAD_LEFT);
                $subtotal = $item['price'] * $item['quantity'];

                DB::table('order_details')->insert([
                    'id' => $detailId,
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'variant' => $item['variant'] ?? null,
                    'unit_price' => $item['price'],
                    'subtotal' => $subtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Buat Payment Record
            $paymentCount = DB::table('payments')->count();
            $paymentId = 'PM' . str_pad($paymentCount + 1, 4, '0', STR_PAD_LEFT);

            DB::table('payments')->insert([
                'id' => $paymentId,
                'order_id' => $orderId,
                'amount' => $totalPrice,
                'method' => $validated['payment_method'] === 'qris' ? 'midtrans' : 'cash',
                'payment_type' => $validated['payment_method'],
                'status' => 'pending',
                'date' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update status meja HANYA untuk Dine In (bukan Reservasi)
            // Untuk Reservasi, meja tetap available sampai hari H
            if ($validated['table_id'] && $validated['order_type'] === 'Dine In') {
                DB::table('tables')
                    ->where('id', $validated['table_id'])
                    ->update([
                        'status' => 'occupied',
                        'updated_at' => now()
                    ]);
            }

            DB::commit();

            // Prepare response data
            $responseData = [
                'order_id' => $orderId,
                'payment_id' => $paymentId,
                'total_price' => $totalPrice,
                'status' => 'pending',
                'payment_method' => $validated['payment_method'],
            ];

            // Add booking code for reservations
            if ($validated['order_type'] === 'Reservasi' && !empty($reservationId)) {
                $reservation = \App\Models\Reservation::find($reservationId);
                $responseData['reservation_id'] = $reservationId;
                $responseData['booking_code'] = $reservation->booking_code;
            }

            return response()->json([
                'success' => true,
                'message' => $validated['order_type'] === 'Reservasi'
                    ? 'Reservasi dan order berhasil dibuat.'
                    : 'Order berhasil dibuat',
                'data' => $responseData
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat order',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    /**
     * Cancel order and rollback all related data
     * Used when payment is cancelled/failed
     * Endpoint: DELETE /api/guest/orders/{orderId}/cancel
     */
    public function cancelOrder($orderId)
    {
        try {
            DB::beginTransaction();

            // Find order
            $order = DB::table('orders')->where('id', $orderId)->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order tidak ditemukan'
                ], 404);
            }

            // Delete payment record
            DB::table('payments')->where('order_id', $orderId)->delete();

            // Delete order details
            DB::table('order_details')->where('order_id', $orderId)->delete();

            // Delete reservation if exists
            DB::table('reservations')->where('order_id', $orderId)->delete();

            // Delete order
            DB::table('orders')->where('id', $orderId)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dibatalkan dan data telah dihapus'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order cancellation failed:', [
                'order_id' => $orderId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
