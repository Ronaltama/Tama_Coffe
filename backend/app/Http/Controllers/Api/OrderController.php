<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            // Validasi input
            $validated = $request->validate([
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'nullable|string|max:20',
                'customer_email' => 'nullable|email',
                'table_id' => 'nullable|string|exists:tables,id',
                'table_number' => 'nullable|string',
                'number_of_people' => 'nullable|integer|min:1',
                'order_type' => 'required|in:Dine In,Reservasi',
                'payment_method' => 'required|in:cash,qris',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|string|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.variant' => 'nullable|string',
                'items.*.price' => 'required|numeric|min:0',
                'reservation_date' => 'required_if:order_type,Reservasi|nullable|date|after:today',
                'reservation_time' => 'required_if:order_type,Reservasi|nullable',
            ]);

            // Additional Validation for Reservation
            if ($validated['order_type'] === 'Reservasi') {
                // Check if table is available
                // This is a basic check. You might want to check against existing reservations.
                // For now, we rely on the frontend to show available tables.
                // But we should verify if the table is actually available in the database if table_id is provided.
                if (!empty($validated['table_id'])) {
                    $isReserved = \App\Models\Reservation::where('date', $validated['reservation_date'])
                        ->where('table_id', $validated['table_id'])
                        ->whereIn('status', ['pending', 'confirmed'])
                        ->exists();
                    
                    if ($isReserved) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Meja sudah direservasi pada tanggal tersebut.'
                        ], 422);
                    }
                }
            }

            DB::beginTransaction();

            // Generate Order ID
            $orderCount = DB::table('orders')->count();
            $orderId = 'OR' . str_pad($orderCount + 1, 4, '0', STR_PAD_LEFT);

            // Hitung total price
            $totalPrice = 0;
            foreach ($validated['items'] as $item) {
                $totalPrice += $item['price'] * $item['quantity'];
            }

            // Cari user_id default (ambil admin pertama untuk log)
            $defaultUser = DB::table('users')
                ->where('role_id', 'RL002') // Role Admin
                ->first();

            if (!$defaultUser) {
                $defaultUser = DB::table('users')->first();
            }

            $defaultUserId = $defaultUser ? $defaultUser->id : null;

            // Buat Order
            DB::table('orders')->insert([
                'id' => $orderId,
                'user_id' => $defaultUserId,
                'table_id' => $validated['table_id'] ?? null,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'] ?? '',
                'total_price' => $totalPrice,
                'note' => $validated['notes'] ?? null,
                'note' => $validated['notes'] ?? null,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Jika order type adalah reservasi, simpan data reservasi
            if ($validated['order_type'] === 'Reservasi') {
                \App\Models\Reservation::create([
                    'id' => 'RES' . time() . rand(100, 999),
                    'user_id' => $defaultUserId, // Use defaultUserId or adjust as needed for guest
                    'order_id' => $orderId, // Link to the newly created order
                    'name' => $validated['customer_name'],
                    'phone' => $validated['customer_phone'],
                    // 'people_count' => $validated['number_of_people'], // Removed column
                    'table_id' => $validated['table_id'] ?? null, // Save selected table
                    'date' => $request->reservation_date,
                    'time' => $request->reservation_time,
                    'status' => 'pending'
                ]);
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
                'status' => $validated['payment_method'] === 'cash' ? 'pending' : 'pending',
                'date' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update status meja jadi occupied (jika ada table_id)
            if ($validated['table_id']) {
                DB::table('tables')
                    ->where('id', $validated['table_id'])
                    ->update([
                        'status' => 'occupied',
                        'updated_at' => now()
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dibuat',
                'data' => [
                    'order_id' => $orderId,
                    'payment_id' => $paymentId,
                    'total_price' => $totalPrice,
                    'status' => 'pending',
                    'payment_method' => $validated['payment_method'],
                ]
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
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
